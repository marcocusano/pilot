<?php

    // Home View
    $router->ANY("/", "home");

    // Installation View
    $router->GET("/install", "install");

    // API Views
    if (isset($configs->get()["options"]["enableAuth"]) && $configs->get()["options"]["enableAuth"]) {
        # $router->POST($defaultEndpoint . '/auth/token', 'api/auth/token');
        # $router->POST($defaultEndpoint . '/auth/code', 'api/auth/code');
    }
    $defaultEndpoint = $configs->get()["options"]["defaultEndpoint"];
    $router->ANY($defaultEndpoint . '/pilot/$table/$show/relations/$tableRelation', 'api/pilot/show');
    $router->ANY($defaultEndpoint . '/pilot/$table/$show/$extra', 'api/pilot/show');
    $router->ANY($defaultEndpoint . '/pilot/$table/$show', 'api/pilot/show');
    $router->ANY($defaultEndpoint . '/pilot/$table', 'api/pilot/index');
    if (isset($configs->get()["options"]["enableQuery"]) && $configs->get()["options"]["enableQuery"]) { $router->GET($defaultEndpoint . '/query', 'api/query'); }

    // Debugging or Test views
    # $router->GET("/debug", "debug"); // Uncomment me to unlock the Debugging Mode, then visit /debug (Under development, I'm sorry)
    # $router->GET("/test", "test"); // Uncomment me to unlock the Test Mode, then visit /test (Here you can use Guzzle Library to test your requests)

    // Errors
    $router->ANY("/403", "403");
    $router->ANY("/404", "404");

?>