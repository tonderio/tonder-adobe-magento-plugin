<?php

namespace Tonder\Payment\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class TestPost extends Action
{
    public function execute()
    {
        $postData = $this->_request->getPostValue();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $postData['api_url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData['body_data'],
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic '. base64_encode($postData['username'] . ":". $postData['password']),
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $responseData = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $responseData->setData([
            'body' => $response
        ]);

        return $responseData;
    }
}