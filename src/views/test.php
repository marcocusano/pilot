<?php
    // Initialize
    header('Content-Type: text/html; charset=utf-8');
    global $configs;
    $client = New \GuzzleHttp\Client([ "base_uri" => $configs->get()["options"]["host"] ]);

    // POST
    echo("<h1>POST:</h1>");
    $newUser = [
         "json" => [ // Use "form_params" instead of "json", to send data to the $_POST directly. both are a mandatory for GuzzleHttp to send the real params like "fullname" and more...
            "fullname" => "Created by /test"
        ]
    ];
    try {
        $post = $client->POST("/api/pilot/users", $newUser);
        var_dump(json_decode($post->getBody()->getContents())); 
    } catch (\GuzzleHttp\Exception\ClientException $error) { var_dump(json_decode($error->getResponse()->getBody()->getContents())); }
    echo "<br><br>";

    // GET
    echo "<h1>GET INDEX:</h1>";
    try {
        $users = json_decode($client->GET("/api/pilot/users")->getBody()->getContents(), true);
        var_dump($users);
    } catch (\GuzzleHttp\Exception\ClientException $error) { var_dump(json_decode($error->getResponse()->getBody()->getContents())); }
    echo "<br><br>";

    // Get USER ID created by the POST method and retrieved by the GET method.
    $userID = $users["data"][count($users["data"]) - 1]["id"];
    // Create a QueryOptions that can be used in any index conditional request method (patchIndex deleteIndex)
    // Please check the /* Alternatives */ to know more.
    $QueryOptions = array( "options" => [ "where" => "id=$userID" ]);

    // GET SHOW
    echo "<h1>GET SHOW: </h1>";
    try {
        $user = $client->GET("/api/pilot/users/$userID");
        var_dump(json_decode($user->getBody()->getContents()));
    } catch (\GuzzleHttp\Exception\ClientException $error) { var_dump(json_decode($error->getResponse()->getBody()->getContents())); }
    echo "<br><br>";

    // PATCH
    // This method can be used only for PRIMARY_KEY or UNIQUE columns
    // You can even change the primary_key value by sending the ?primary_key={key} query param, as described by the request below.
    echo "<h1>PATCH:</h1>";
    $userData = [
        "json" => [
            "fullname" => "Changed by /test"
        ]
    ];
    try {
        $patch = $client->PATCH("/api/pilot/users/$userID", $userData);
        var_dump(json_decode($patch->getBody()->getContents()));
        /*
            // An alternative version is the patchIndex method that can be used by sending the PATCH request directly to the index request as following:
            $patch = $client->PATCH("/api/pilot/users?" . http_build_query($QueryOptions), $userData);
            var_dump(json_decode($patch->getBody()->getContents())); echo "<br><br>";
        */
    } catch (\GuzzleHttp\Exception\ClientException $error) { var_dump(json_decode($error->getResponse()->getBody()->getContents())); }
    echo "<br><br>";

    // DELETE
    // This method can be used only for PRIMARY_KEY or UNIQUE columns
    // You can even change the primary_key value by sending the ?primary_key={key} query param, as described by the request below.
    // If you've to delete relations or rows using a custom condition, please use the deleteIndex method described below.
    echo "<h1>DELETE:</h1>";
    try {
        $delete = $client->DELETE("/api/pilot/users/$userID");
        var_dump(json_decode($delete->getBody()->getContents()));
        /*
            // An alternative version is the deleteIndex method that can be used by sending the DELETE request directly to the index request as following:
            $delete = $client->DELETE("/api/pilot/users?" . http_build_query($QueryOptions));
            var_dump(json_decode($delete->getBody()->getContents()));
        */
    } catch (\GuzzleHttp\Exception\ClientException $error) { var_dump(json_decode($error->getResponse()->getBody()->getContents())); }
    echo "<br><br>";

    // DELETE AGAIN
    echo "<h1>DELETE INDEX (204 - No Content):</h1>";
    try {
        $delete = $client->DELETE("/api/pilot/users?" . http_build_query($QueryOptions));
        var_dump($delete->getBody()->getContents());
    } catch (\GuzzleHttp\Exception\ClientException $error) { var_dump(json_decode($error->getResponse()->getBody()->getContents())); }
    

?>