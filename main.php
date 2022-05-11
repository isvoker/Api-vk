<?php

    $redirect_uri = '...';
    $client_id = '...';

    $oauth = new VKOAuth();
    $display = VKOAuthDisplay::PAGE;
    $scope = array(VKOAuthUserScope::WALL, VKOAuthUserScope::MARKET, VKOAuthUserScope::PHOTOS, VKOAuthUserScope::offline);
    $response_type = 'code';
    $state = 'secret_state_code';
    $browser_url = $oauth->getAuthorizeUrl(VKOAuthResponseType::CODE, $client_id, $redirect_uri, $display, $scope, $state);

    function getTokken() {
        $_SERVER['REQUEST_URI'];

        if (!$_GET['code']){
            return;
        }

        $redirect_uri = '...';

        $client_id = '...';

        $code = $_GET['code'];
        $oauth = new VKOAuth();
        $client_secret = '...';
        $response = $oauth->getAccessToken($client_id, $client_secret, $redirect_uri, $code);
        $access_token = $response['access_token'];
        Logger::viewVar($access_token);

        DBCommand::doUpdate(
            '...',
            ['value' => $access_token],
            'ident = ' . DBCommand::qV('access_token')
        );
        header("Location: '...'");
    };
  getTokken();

    $token = DBCommand::doSelect(
        array(
            'select' => 'value',
            'from' => '...',
        ), 'row'
    );
    $access_token = $token['value'];

    $vk = new VKApiClient();

    try {
        $groups = $vk->Market()->getAlbums($access_token, array(
            'owner_id' => '...',
        ));
    } catch (Throwable $e) {
        echo 'Необходимо обновить сессионный токен!';

        Application::assign([
            'browser_url' => $browser_url,
        ]);
        Application::showContent( 'VkApi/tpl/'...'' );

        return;
    }
