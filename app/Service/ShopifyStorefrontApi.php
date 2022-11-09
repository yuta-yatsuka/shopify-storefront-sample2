<?php
namespace App\Service;


class ShopifyStorefrontApi
{
    /** @var string */
    private $host;

    /** @var string */
    private $accessToken;

    /** @var string */
    private $version;

    public function __construct()
    {
        $this->host = env('SHOPIFY_HOST');
        $this->accessToken = env('SHOPIFY_STOREFRONT_ACCESS_TOKEN');
        $this->version = '2022-10';
    }

    /**
     * カスタマーアクセストークンから顧客情報を取得
     * @param string $accessToken
     * @return mixed
     */
    public function getCustomer(string $accessToken){
        $query = <<<QUERY
{
    customer(customerAccessToken:"$accessToken"){
        id
        defaultAddress{
            address1
            address2
            city
            country
            phone
            province
            zip
            firstName
            lastName
        }
    }
}
QUERY;

        $response = $this->execCurl($query);
        return $response->data->customer;
    }
    /**
     * 顧客情報を作成する
     * @param string $email
     * @param string $password
     * @return void
     */
    public function createCustomer(string $email, string $password)
    {
        $query = <<<QUERY
mutation customerCreate(\$input: CustomerCreateInput!) {
    customerCreate(input: \$input) {
        customer {
            email
        }
        customerUserErrors {
            message
        }
    }
}
QUERY;

        $variables = [
            'input' => [
                'email' => $email,
                'password' => $password,
            ]
        ];

        $response = $this->execCurl($query, $variables);
    }

    /**
     * 顧客情報のアクセストークンを取得
     * @param string $email
     * @param string $password
     * @return string
     */
    public function getCustomerAccessToken(string $email, string $password)
    {
        $query = <<<QUERY
mutation customerAccessTokenCreate(\$input: CustomerAccessTokenCreateInput!) {
    customerAccessTokenCreate(input: \$input) {
        customerAccessToken {
            accessToken
        }
        customerUserErrors {
            message
            field
        }
    }
}
QUERY;

        $variables = [
            'input' => [
                'email' => $email,
                'password' => $password
            ]
        ];

        $response = $this->execCurl($query, $variables);

        return $response->data->customerAccessTokenCreate->customerAccessToken->accessToken;
    }

    /**
     * 商品を全て取得
     * @return object
     */
    public function getProducts()
    {
        $query = <<<QUERY
{
    products (first: 50) {
        edges {
            node {
                id
                title
                variants(first: 10){
                    edges {
                        node {
                            id
                            title
                        }
                    }
                }
            }
        }
    }
}
QUERY;

        $response = $this->execCurl($query);

        return $response->data->products;
    }

    /**
     * IDから商品を取得
     * @param string $productId
     * @return mixed
     */
    public function getProductById(string $productId)
    {
        $query = <<<QUERY
{
    product(id: "$productId"){
        id
        title
        variants(first: 20){
            edges{
                node{
                    title
                    id
                }
            }
        }
    }
}
QUERY;

        $response = $this->execCurl($query);

        return $response->data->product;
    }

    /**
     * チェックアウトを作成
     * @return mixed
     */
    public function createCheckout(){
        $query = <<<QUERY
mutation checkoutCreate(\$input: CheckoutCreateInput!) {
    checkoutCreate(input: \$input) {
        checkout {
            id
            webUrl
            lineItems(first: 20){
                edges{
                    node{
                        id
                        title
                        quantity
                        variant{
                            id
                            title
                        }
                    }
                }
            }
        }
        checkoutUserErrors {
            message
            field
        }
    }
}
QUERY;

        $variables = [
            'input' => [
                'allowPartialAddresses' => false
            ]
        ];

        $response = $this->execCurl($query, $variables);

        return $response->data->checkoutCreate->checkout;
    }


    /**
     * IDからチェックアウトを取得
     * @param string|null $checkoutId
     * @return null
     * @throws \Exception
     */
    public function getCheckout(?string $checkoutId){
        if(!$checkoutId) return null;
        $query = <<<QUERY
{
    node(id: "$checkoutId"){
        ... on Checkout{
            id
            webUrl
            completedAt
            lineItems(first: 20){
                edges{
                    node{
                        id
                        title
                        quantity
                        variant{
                            id
                            title
                            price{
                                amount
                            }
                        }
                    }
                }
            }
        }
    }
}
QUERY;

        $response = $this->execCurl($query);

        if(isset($response->errors)){
            throw new \Exception('error',);
        }

        if($response->data->node->completedAt){
            return null;
        }

        return $response->data->node;
    }

    /**
     * チェックアウトに顧客を紐付け
     * @param string $checkoutId
     * @param string $accessToken
     * @return void
     */
    public function checkoutCustomerAssociate(string $checkoutId, string $accessToken){
        $query = <<<QUERY
mutation checkoutCustomerAssociateV2(\$checkoutId: ID!, \$customerAccessToken: String!) {
    checkoutCustomerAssociateV2(checkoutId: \$checkoutId, customerAccessToken: \$customerAccessToken) {
        checkoutUserErrors {
            message
            field
        }
    }
}
QUERY;

        $variables = [
            'checkoutId' => $checkoutId,
            'customerAccessToken' => $accessToken
        ];

        $this->execCurl($query, $variables);
    }

    /**
     * チェックアウトに商品を追加
     * @param string $checkoutId
     * @param string $itemId
     * @return void
     */
    public function addItemToCheckout(string $checkoutId, string $itemId){
        $query = <<< QUERY
mutation checkoutLineItemsAdd(\$checkoutId: ID!, \$lineItems: [CheckoutLineItemInput!]!) {
    checkoutLineItemsAdd(checkoutId: \$checkoutId, lineItems: \$lineItems) {
        checkoutUserErrors {
            message
            field
        }
    }
}
QUERY;

        $variables = [
            'checkoutId' => $checkoutId,
            'lineItems' => [
                'variantId' => $itemId,
                'quantity' => 1
            ]
        ];

        $this->execCurl($query, $variables);
    }

    /**
     * チェックアウトから商品を削除
     * @param string $checkoutId
     * @param string $itemId
     * @return void
     * @throws \Exception
     */
    public function removeItemFromCheckout(string $checkoutId, string $itemId)
    {
        $query = <<<QUERY
mutation checkoutLineItemsRemove(\$checkoutId: ID!, \$lineItemIds: [ID!]!) {
    checkoutLineItemsRemove(checkoutId: \$checkoutId, lineItemIds: \$lineItemIds) {
        checkoutUserErrors {
            message
            field
        }
    }
}
QUERY;

        $variables = [
            'checkoutId' => $checkoutId,
            'lineItemIds' => [
                $itemId
            ]
        ];


        $response = $this->execCurl($query, $variables);


        $errors = $response->data->checkoutLineItemsRemove->checkoutUserErrors;
        if ($errors){
            $errorMessages = "";
            foreach ($errors as $e){
                $errorMessages .= $e . "\n";
            }
            throw new \Exception($errorMessages);
        }

    }



    /**
     * cURLを実行
     * @param string $query
     * @param array|null $variables
     * @return mixed
     */
    protected function execCurl(string $query, array $variables = null)
    {
        $headers = [
            'X-Shopify-Storefront-Access-Token: ' . $this->accessToken
        ];

        if($variables){
            $postFields = json_encode(['query' => $query, 'variables' => $variables]);
            $headers[] = 'Content-type: application/json';
        }else{
            $postFields = $query;
            $headers[] = 'Content-type: application/graphql';
        }

        $url = 'https://' . $this->host . '/api/' . $this->version . '/graphql.json';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response);


        return $this->edgesToArray($response);
    }

    /**
     * GraphQLのレスポンスないのedgesを配列に変換
     * @param object $target
     * @return object
     */
    protected function edgesToArray(object $target){
        if(property_exists($target, 'edges')){
            $edges = $target->edges;
            $edgesArray = [];
            foreach($edges as $edge){
                $edgesArray[] = $this->edgesToArray($edge->node);
            }
            $result = $edgesArray;
        }else{
            $result = $target;
            foreach ($target as $key => $p){
                if(gettype($p) === 'object'){
                    $result->$key = $this->edgesToArray($p);
                }
            }
        }
        return $result;
    }
}
