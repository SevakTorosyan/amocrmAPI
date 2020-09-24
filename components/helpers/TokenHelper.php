<?php

namespace app\components\helpers;

use Exception;
use Yii;

/**
 * Class AccessTokenHelper
 * @package app\components\helpers
 */
class TokenHelper
{
    /**
     * @param string $code
     * @param bool $force
     * @return array
     * @throws Exception
     */
    public static function getAccessToken(string $code = '', bool $force = false): array
    {
        $session = Yii::$app->session;
        $config = Yii::$app->params;
        if ($session->get('access_token') && !$force) {
            return [
                'access_token' => $session->get('access_token'),
                'refresh_token' => $session->get('refresh_token'),
                'expires_in' => $session->get('expires_in'),
                'token_type' => $session->get('token_type'),
                'resource_owner_id' => $config['clientId'],
            ];
        }
        $subdomain = $config['subdomain']; //Поддомен нужного аккаунта
        $link = 'https://' . $subdomain . '.amocrm.ru/oauth2/access_token'; //Формируем URL для запроса

        $data = [
            'client_id'     => $config['clientId'],
            'client_secret' => $config['clientSecret'],
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $config['redirectUri'],
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $code = (int)$code;
        $errors = [
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable',
        ];
        if ($code < 200 || $code > 204) {
            throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
        }
        $response = json_decode($out, true);
        $response[] = $config['clientId'];

        $access_token = $response['access_token'];
        $refresh_token = $response['refresh_token'];
        $token_type = $response['token_type'];
        $expires_in = $response['expires_in'];


        $session->open();
        $session->set('access_token', $access_token);
        $session->set('refresh_token', $refresh_token);
        $session->set('token_type', $token_type);
        $session->set('expires_in', $expires_in);

        return $response;
    }
}
