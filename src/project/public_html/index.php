<?php

require_once(__DIR__ . '/../bootstrap.php');

$slimSettings = array('determineRouteBeforeAppMiddleware' => true);

if (in_array(ENVIRONMENT, array('dev')))
{
    $slimSettings['displayErrorDetails'] = true;
}

$slimConfig = array('settings' => $slimSettings);
$app = new Slim\App($slimConfig);

# Specify the routes that don't require you to be logged in.
$publicRoutes = array(
    'home',
    'login',
    'logout',
    'handle-sso-login',
    'handle-sso-logout',
    'validate',
);

// Apply any middleware
// Middleware you add last will be executed first.
$app->add(new CheckLoggedInMiddleware($publicRoutes, '/login', SiteSpecific::isLoggedIn()));
$app->add(new TrailingSlashMiddleware());
$app->add(new HttpsMiddleware());


$app->get('/', function (Slim\Http\Request $request, \Slim\Http\Response $response, $args) {
    
    $data = array(
        "result" => "success",
        "routes" => array(
            '/validate',
            '/login',
            '/logout',
            '/admin',
        )
    );
    
    return SiteSpecific::getJsonResponse($data, $response, 200);
})->setName('home');


# Display the login page.
$app->get('/login', function (Slim\Http\Request $request, \Slim\Http\Response $response, $args) {
    $controller = new SessionController($request, $response, $args);
    return $controller->login();
})->setName('login');


# Handle the user trying to logout.
$app->get('/logout', function (Slim\Http\Request $request, \Slim\Http\Response $response, $args) {
    $controller = new SessionController($request, $response, $args);
    return $controller->logout();
})->setName('logout');

# Handle the SSO sending us a logout request.
$app->post('/logout', function (Slim\Http\Request $request, \Slim\Http\Response $response, $args) {
    $controller = new SessionController($request, $response, $args);
    return $controller->handleSsologout();
})->setName('handle-sso-logout');



$app->group('/admin', function () {
    
    // Start of /admin/rule-group
    $this->group('/rule-group', function () {
        # Create a rule group
        $this->get('/create', function ($request, $response, $args) {
            $controller = new RuleGroupController($request, $response);
            return $controller->create();
        })->setName('rule-group-page');
        
        # Get an existing rule group
        $this->get('/{id}', function ($request, $response, $args) {
            $ruleGroupId = intval($args['id']);
            $controller = new RuleGroupController($request, $response);
            return $controller->edit($ruleGroupId);
        })->setName('rule-group-page');

        // edit an existing rule group
        $this->post('/{id}', function ($request, $response, $args) {
            $ruleGroupId = intval($args['id']);
            $controller = new RuleGroupController($request, $response, $args);
            return $controller->handleEdit($ruleGroupId);
        })->setName('rule-group-edit-handler');
        
        # overview of rule groups
        $this->get('', function ($request, $response, $args) {
            $controller = new RuleGroupController($request, $response);
            return $controller->overview();
        })->setName('rule-group-page');
        
        // create a new rule group
        $this->post('', function ($request, $response, $args) {
            $controller = new RuleGroupController($request, $response);
            return $controller->handleCreate();
        })->setName('rule-group-create-handler');
    });
    // end of /admin/rule-group
    
    
    // Start of /admin/field
    $this->group('/field', function () {        
        // edit an existing field
        $this->post('/{id}', function ($request, $response, $args) {
            $ruleGroupId = intval($args['id']);
            $controller = new FieldController($request, $response, $args);
            return $controller->handleEdit($ruleGroupId);
        })->setName('field-edit-handler');
        
        $this->get('/{id}/delete', function ($request, $response, $args) {
            $fieldID = intval($args['id']);
            $controller = new FieldController($request, $response, $args);
            return $controller->delete($fieldID);
        })->setName('field-edit-handler');
        
        $this->delete('/{id}', function ($request, $response, $args) {
            $fieldID = intval($args['id']);
            $controller = new FieldController($request, $response, $args);
            return $controller->delete($fieldID);
        })->setName('field-edit-handler');
        
        # overview of existing fields
        $this->get('', function ($request, $response, $args) {
            $controller = new FieldController($request, $response);
            return $controller->overview();
        })->setName('field-page');
        
        // create a new field
        $this->post('', function ($request, $response, $args) {
            $controller = new FieldController($request, $response);
            return $controller->create();
        })->setName('field-create-handler');
    });
    // end of /admin/rule-group
    
    
    // Start of /admin/csv-type
    $this->group('/csv-type', function () {  
        
        $this->get('/{id}/delete', function ($request, $response, $args) {
            $fieldID = intval($args['id']);
            $controller = new CsvTypeController($request, $response, $args);
            return $controller->delete($fieldID);
        })->setName('csv-type-edit-handler');
        
        # Show a page to create a new csv spec.
        $this->get('/create', function ($request, $response, $args) {
            $controller = new CsvTypeController($request, $response);
            return $controller->create();
        })->setName('csv-type-page');
        
        // edit an existing csv type
        $this->get('/{id}', function ($request, $response, $args) {
            $ruleGroupId = intval($args['id']);
            $controller = new CsvTypeController($request, $response, $args);
            return $controller->edit($ruleGroupId);
        })->setName('csv-type-edit-view');
        
        $this->delete('/{id}', function ($request, $response, $args) {
            $fieldID = intval($args['id']);
            $controller = new CsvTypeController($request, $response, $args);
            return $controller->delete($fieldID);
        })->setName('csv-type-edit-handler');
        
        // update an existing csv type
        $this->post('/{id}', function ($request, $response, $args) {
            $ruleGroupId = intval($args['id']);
            $controller = new CsvTypeController($request, $response, $args);
            return $controller->handleCreateSubmit($ruleGroupId);
        })->setName('csv-type-edit-handler');
        
        # overview of existing fields
        $this->get('', function ($request, $response, $args) {
            $controller = new CsvTypeController($request, $response);
            return $controller->overview();
        })->setName('csv-type-page');
        
        // create a new csv type
        $this->post('', function ($request, $response, $args) {
            $controller = new CsvTypeController($request, $response);
            return $controller->handleCreateSubmit();
        })->setName('csv-type-create-handler');
    });
    // end of /admin/rule-group
    
    
    $this->get('/attribute-rule', function ($request, $response, $args) {
        print('Attribute rule management page');
    })->setName('attribute-page');
    
    $this->get('', function ($request, $response, $args) {
        $data = array(
            "result" => "success",
            "routes" => array(
                '/rule-group',
                '/field',
                '/csv-type',
            )
        );

        return SiteSpecific::getJsonResponse($data, $response, 200);
    })->setName('admin-page');
});


$app->post('/validate', function ($request, $response, $args) {
    $validatorController = new ValidatorController($request, $response, $args);
    return $validatorController->validate();
})->setName('validate');

$app->run();
