<?php
header('Content-Type: text/html; charset=utf-8');
global $configs;
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Pilot Framework installation step by step guide.">
    <meta name="author" content="Marco Cusano">
    <title>Pilot Framework Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body>
    <?php if ($_SERVER["REQUEST_METHOD"] === "GET" && $configs->get()["installation"]["required"]) { ?>
        <form id="installation-form" class="p-0 mx-auto my-5 d-flex justify-content-center" method="POST" style="max-width: 800px">
            <div class="card">
                <div class="card-header">
                    <h1><img src="https://www.php.net/images/php8/logo_php8_2.svg" width="100" class="mx-2 p-2 bg-primary" /> Pilot Framework Installation</h1>
                    <h4>Welcome to the step-by-step Pilot Framework installation guide.</h4>
                </div>
                <!-- Database -->
                <div class="card-body">
                    <div class="fs-2">
                        <p>Database Configuration</p>
                    </div>
                    <div class="row g-3">
                        <!-- Host -->
                        <div class="col-lg-4 col-md-4 col-sm-8 col-8">
                            <div class="form-container">
                                <label class="form-label">Host <strong class="text-danger">*</strong></label>
                                <input class="form-control" type="text" name="data[database][host]" placeholder="localhost" value="<?php echo $configs->get()["database"]["host"]; ?>" required></input>
                            </div>
                        </div>
                        <!-- Port -->
                        <div class="col-lg-2 col-md-2 col-sm-4 col-4">
                            <div class="form-container">
                                <label class="form-label">Port <strong class="text-danger">*</strong></label>
                                <input class="form-control" type="number" name="data[database][port]" placeholder="3306" value="<?php echo $configs->get()["database"]["port"]; ?>" required></input>
                            </div>
                        </div>
                        <!-- Name -->
                        <div class="col-lg-3 col-md-3 col-6">
                            <div class="form-container">
                                <label class="form-label">Name <strong class="text-danger">*</strong></label>
                                <input class="form-control" type="text" name="data[database][name]" placeholder="databaseName" value="<?php echo $configs->get()["database"]["name"]; ?>" required></input>
                            </div>
                        </div>
                        <!-- Prefix -->
                        <div class="col-lg-3 col-md-3 col-6" title="Just a string like 'prefix'. (a-Z)">
                            <div class="form-container">
                                <label class="form-label">Table Prefix <strong class="text-danger">*</strong></label>
                                <input class="form-control" type="text" name="data[database][prefix]" placeholder="{prefix}_tableName" value="<?php echo $configs->get()["database"]["prefix"]; ?>" required></input>
                            </div>
                        </div>
                        <!-- Username -->
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="form-container">
                                <label class="form-label">Username <strong class="text-danger">*</strong></label>
                                <input class="form-control" type="text" name="data[database][user]" placeholder="root" value="<?php echo $configs->get()["database"]["user"]; ?>" required></input>
                            </div>
                        </div>
                        <!-- Password -->
                        <div class="col-lg-6 col-md-6 col-12">
                            <div class="form-container">
                                <label class="form-label">Password <strong class="text-danger">*</strong></label>
                                <input class="form-control" type="password" name="data[database][password]" value="<?php echo $configs->get()["database"]["password"]; ?>" placeholder="password"></input>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Token Hash -->
                <div class="card-body">
                    <p class="fs-2">Token Hash</p>
                    <p>If you doesn't have an idea on how to configure the token hash, just leave this area to the default configurations.</p>
                    <div class="row g-3">
                        <!-- Host -->
                        <div class="col-lg-4 col-md-6 col-12" title="Use default hash() PHP method to generate a random key.">
                            <div class="form-container">
                                <label class="form-label">Hash method <strong class="text-danger">*</strong></label>
                                <input class="form-control" type="text" name="data[applications][hash]" value="<?php echo $configs->get()["applications"]["hash"]; ?>" placeholder="sha256" required></input>
                            </div>
                        </div>
                        <!-- Description -->
                        <div class="col-lg-8 col-md-6 col-12" title="Use default date() PHP method that will be added to your token, during the generation.">
                            <div class="form-container">
                                <label class="form-label">Datetime Format <strong class="text-danger">*</strong></label>
                                <input class="form-control" type="string" name="data[applications][datetimeFormat]" value="<?php echo $configs->get()["applications"]["datetimeFormat"]; ?>" placeholder="YYYYmmddHHiiss" required></input>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Options -->
                <div class="card-body">
                    <p class="fs-2">Options</p>
                    <div class="row g-3">
                        <!-- Enable Auth Usage -->
                        <div class="col-12" title="By using your '/endpoint/auth' you'll be able to request a code to generate a token (for users).">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="check_auth" name="data[options][enableAuth]">
                                <label class="form-check-label" for="flexSwitchCheckDefault">Enable Auth functions <strong>(Under development)</strong></label>
                            </div>
                        </div>
                        <!-- Enable Query Usage -->
                        <div class="col-12" title="By using your '/endpoint/query?query={queryData}' you'll be able to send custom queries to your database.">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="check_query" name="data[options][enableQuery]">
                                <label class="form-check-label" for="flexSwitchCheckDefault">Enable Query functions</label>
                            </div>
                        </div>
                        <!-- Hostname -->
                        <div class="col-lg-8 col-md-6 col-12" title="Placeholder base on '$_SERVER[HTTP_HOST]'.">
                            <div class="form-container">
                                <label class="form-label">Hostname <strong class="text-danger">*</strong></label>
                                <input class="form-control" type="text" name="data[options][host]" placeholder="http://<?php echo $_SERVER['HTTP_HOST']; ?>" value="<?php echo $configs->get()["options"]["host"]; ?>" required></input>
                            </div>
                        </div>
                        <!-- Locale -->
                        <div class="col-lg-4 col-md-6 col-12" title="Pilot Framework Translation">
                            <div class="form-container">
                                <label class="form-label">Locale <strong class="text-danger">*</strong></label>
                                <select class="form-control form-select" name="data[options][locale]" required>
                                    <option value="en">English</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Template -->
                <div class="card-body">
                    <p class="fs-2">Template</p>
                    <p>The Template will be used to initialize the installation with a certain default configurations.</p>
                    <p><strong>Scratch:</strong> Empty <code>schema.json</code>. Just <code>#__api</code> table to the Database.</p>
                    <p><strong>Test Mode:</strong> Pre-configured <code>schema.json</code>. Will be installed <code>#__api</code>, <code>#__test_users</code> and <code>#__test_user_documents</code> tables to the Database, for testing purpose. Also, you must unlock the route <code><?php echo $_SERVER['HTTP_HOST']; ?>/test</code> from <code>/routes.php</code>.</p>
                    <div class="row g-3">
                        <!-- Template Selector -->
                        <div class="col-12" title="Select a Template to be installed">
                            <div class="form-container">
                                <label class="form-label">Select a Template <strong class="text-danger">*</strong></label>
                                <select class="form-control form-select" name="template" required>
                                    <?php foreach((New \Pilot\Installation\Templates)->getTemplates() as $template) { ?>
                                        <option value="<?php echo $template["name"]; ?>"><?php echo $template["display_name"]; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div class="card-footer d-flex flex-row-reverse">
                    <button class="btn btn-primary float-right" type="submit">Install Pilot Framework</button>
                </div>
            </div>
        </form>
    <?php } else if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $response = (new \Pilot\Installation)->install(); ?>
        <div class="p-0 mx-auto my-5 d-flex justify-content-center" method="POST" style="max-width: 800px">
            <div class="card">
                <div class="card-header">
                    <h1><img src="https://www.php.net/images/php8/logo_php8_2.svg" width="100" class="mx-2 p-2 bg-primary" /> Pilot Framework Installed!</h1>
                    <h4 class="text-center"><?php echo $response->getTitle(); ?></h4>
                </div>
                <!-- Content -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 text-center">
                            <?php
                            switch ($response->getType()) {
                                case \Pilot\Installation\ResponseType::ERROR:
                                case \Pilot\Installation\ResponseType::TEXT:
                                    echo "<p>" . $response->getMessage() . "</p>";
                                    break;
                                case \Pilot\Installation\ResponseType::INPUT:
                                    echo '<input class="form-control text-center" value="' . $response->getMessage() . '" readonly></input>';
                                    break;
                                default:
                                    echo "<p>There was an internal error. Maybe a bug (?)</p>";
                                    break;
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div class="card-footer d-flex justify-content-center">
                    <a class="btn btn-primary float-right" href="<?php echo $response->getRedirect(); ?>"><?php echo $response->getActionText(); ?></a>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="p-0 mx-auto my-5 d-flex justify-content-center" method="POST" style="max-width: 800px">
            <div class="card">
                <div class="card-body text-center">
                    <p class="mb-0">Method not allowed or installation already completed!</p>
                </div>
                <div class="card-footer"><a href="/" class="btn btn-primary d-block w-100">Go back!</a></div>
            </div>
        </div>
    <?php } ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>