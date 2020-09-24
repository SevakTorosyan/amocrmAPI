<?php

namespace app\controllers;

use AmoCRM\OAuth2\Client\Provider\AmoCRM;
use app\components\helpers\TokenHelper;
use Exception;
use Throwable;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

/**
 * Class SiteController
 * @package app\controllers
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only'  => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @throws \Exception
     */
    public function actionIndex()
    {
        $config = Yii::$app->params;
        $provider = new AmoCRM([
            'clientId'     => $config['clientId'],
            'clientSecret' => $config['clientSecret'],
            'redirectUri'  => $config['redirectUri'],
        ]);
        $session = Yii::$app->session;
        $session->open();
        if ($session->get('access_token')) {
            return $this->render('index');
        }
        $session->set('oauth2state', bin2hex(random_bytes(16)));
        return $this->render('login', compact('provider'));
    }

    /**
     * @return \yii\web\Response
     */
    public function actionCode()
    {
        try {
            $code = Yii::$app->request->get('code');
            if (!$code) {
                throw new Exception('Код не передан');
            }
            TokenHelper::getAccessToken($code, true);

            return $this->goBack('/');
        } catch (Throwable $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * @return string
     */
    public function actionDestroy()
    {
        Yii::$app->session->open();
        Yii::$app->session->destroy();
        return $this->redirect('/');
    }
}
