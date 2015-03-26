<?php

use Silex\Application;
use Silex\Provider;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
    
// Activation du debugging a desactiver en production
$app['debug'] = true;
$app['stopwatch'] = new Stopwatch();

require_once __DIR__.'/../config/config.php';

$app->before(function ($request) use ($app) {
    $app['stopwatch']->start('vespa');
}, Application::EARLY_EVENT);

$app->register(new Provider\DoctrineServiceProvider());
$app->register(new Provider\SecurityServiceProvider());
$app->register(new Provider\RememberMeServiceProvider());
$app->register(new Provider\SessionServiceProvider());
$app->register(new Provider\ServiceControllerServiceProvider());
$app->register(new Provider\UrlGeneratorServiceProvider());
$app->register(new Provider\TwigServiceProvider());
$app->register(new Provider\SwiftmailerServiceProvider());

// Register the SimpleUser service provider.
$simpleUserProvider = new SimpleUser\UserServiceProvider();
$app->register($simpleUserProvider);

// Register Monolog
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.$parameters['monolog']['logfile'],
    'monolog.name' => $parameters['monolog']['name']
));

// Mount the user controller routes:
$app->mount('/user', $simpleUserProvider);

$app->get('/', function() use ($app) {
    return file_get_contents('header.php').file_get_contents('vespa.php').file_get_contents('footer.php');
});

/*****************************************************************************************
 *                                                                                       *
 * Requête de récupération des paramètres d'initialisation de l'interface                *
 *                                                                                       *
 *****************************************************************************************/
$app->post('/Services/Vespa.svc/GetInitializationInfos', function() use ($app) {
    $sql = "SELECT DATE_FORMAT(min(date),'%d/%m/%Y') AS MinDate, DATE_FORMAT(max(date),'%d/%m/%Y') AS MaxDate FROM report";
    $res = $app['db']->fetchAssoc($sql);
    $response['ErrorMessage'] = null;
    $response['ErrorStackTrace'] = null;
    $response['MinDate'] = $res['MinDate'];
    $response['MaxDate'] = $res['MaxDate'];
    return $app->json($response);
});

/*****************************************************************************************
 *                                                                                       *
 * Requête de récupération de la liste des plantes, éventuellement filtré par un mot clé *
 *                                                                                       *
 *****************************************************************************************/
$app->post('/Services/Vespa.svc/GetPlants', function (Request $request) use ($app) {
    // Récupération du filtre text
    $req = json_decode( $request->getContent(), true );
    if ( ( is_null( $req ) ) || (count($req) == 0 ) ) {
        $text = $request->get('text');
    } else {
        $text = $req['text'];
    }
    $searchText = "%".$text."%";
    
    // Construction de la requête
    $sql = "SELECT Id, Name AS Text FROM plant WHERE Name LIKE ? ORDER BY Name";

    // Appel à la BDD
    $results = $app['db']->fetchAll($sql, array( $searchText ) );

    // Reformatage des résultats
    foreach( $results as $res) {
        $items[] = array( "Id"=>(int) $res['Id'], "Text"=>mb_convert_encoding($res['Text'], "UTF-8") );
    }

    // Construction du tableau retour
    $response['ErrorMessage'] = null;
    $response['ErrorStackTrace'] = null;
    $response['Items'] = $items;

    // Conversion de la réponse en JSON et retour
    return $app->json($response);
});

/*****************************************************************************************
 *                                                                                       *
 * Requête de récupération de la liste des bioagresseur filtrée par un mot clé           *
 *                                                                                       *
 *****************************************************************************************/
$app->post('/Services/Vespa.svc/GetBugs', function(Request $request) use ($app) {
    // Récupération du filtre text
    if ( ( is_null( $req ) ) || (count($req) == 0 ) ) {
        $text = $request->get('text');
    } else {
        $text = $req['text'];
    }
    $searchText = "%".$text."%";

    
    // Construction de la requête
    $sql = "SELECT Id, Name AS Text FROM bioagressor WHERE Name LIKE ? ORDER BY Name";

    // Appel à la BDD
    $results = $app['db']->fetchAll($sql, array( $searchText ) );

    // Reformatage des résultats
    foreach( $results as $res) {
        $items[] = array( "Id"=>(int) $res['Id'], "Text"=>mb_convert_encoding($res['Text'], "UTF-8") );
    }

    // Construction du tableau retour
    $response['ErrorMessage'] = null;
    $response['ErrorStackTrace'] = null;
    $response['Items'] = $items;

    // Conversion de la réponse en JSON et retour
    return $app->json($response);
});

/*****************************************************************************************
 *                                                                                       *
 * Requête de récupération de la liste des maladies filtrée par un mot clé               *
 *                                                                                       *
 *****************************************************************************************/
$app->post('/Services/Vespa.svc/GetDiseases', function(Request $request) use ($app) {
    // Récupération du filtre text
    if ( ( is_null( $req ) ) || (count($req) == 0 ) ) {
        $text = $request->get('text');
    } else {
        $text = $req['text'];
    }
    $searchText = "%".$text."%";
    
    // Construction de la requête
    $sql = "SELECT Id, Name AS Text FROM disease WHERE Name LIKE ? ORDER BY Name";

    // Appel à la BDD
    $results = $app['db']->fetchAll($sql, array( $searchText ) );

    // Reformatage des résultats
    foreach( $results as $res) {
        $items[] = array( "Id"=>(int) $res['Id'], "Text"=>mb_convert_encoding($res['Text'], "UTF-8") );
    }

    // Construction du tableau retour
    $response['ErrorMessage'] = null;
    $response['ErrorStackTrace'] = null;
    $response['Items'] = $items;

    // Conversion de la réponse en JSON et retour
    return $app->json($response);
});

/*****************************************************************************************
 *                                                                                       *
 * Requête de récupération des infos sur la zone géographique                            *
 *                                                                                       *
 *****************************************************************************************/
/* Reponse pour la bourgogne après une recherche sur la pomme de terre:
{"ErrorMessage":null,
"ErrorStackTrace":null,
"AreaName":"Bourgogne",
"Bioagressors":[{"Id":40,"Text":"acarien"},{"Id":321,"Text":"adventice"},{"Id":362,"Text":"altise"},{"Id":346,"Text":"altise des crucifères"},{"Id":309,"Text":"campagnol des champs"},{"Id":1,"Text":"carpocapse"},{"Id":30,"Text":"carpocapse des pommes et des poires"},{"Id":189,"Text":"cécidomyie"},{"Id":210,"Text":"charançon"},{"Id":28,"Text":"charançon des siliques de colza"},{"Id":137,"Text":"doryphore"},{"Id":235,"Text":"grosse altise du colza"},{"Id":59,"Text":"hanneton"},{"Id":3,"Text":"méligèthe"},{"Id":338,"Text":"mouche"},{"Id":74,"Text":"mouche de la betterave"},{"Id":280,"Text":"mouche de la carotte"},{"Id":27,"Text":"mouche de la cerise"},{"Id":12,"Text":"mouche de l'oignon"},{"Id":18,"Text":"mouche de l'olive"},{"Id":158,"Text":"mouche grise"},{"Id":198,"Text":"mouche méditerranéenne des fruits"},{"Id":355,"Text":"nématode"},{"Id":167,"Text":"nématode doré"},{"Id":21,"Text":"pou"},{"Id":295,"Text":"puceron"},{"Id":220,"Text":"puceron des épis de céréales"},{"Id":334,"Text":"punaise"},{"Id":272,"Text":"pyrale"},{"Id":99,"Text":"pyrale du maïs"},{"Id":282,"Text":"taupe d'europe"},{"Id":136,"Text":"taupin"},{"Id":179,"Text":"teigne du poireau"},{"Id":44,"Text":"tétranyque"},{"Id":164,"Text":"tordeuse"},{"Id":313,"Text":"tordeuse orientale du pêcher"}],
"Diseases":[{"Id":36,"Text":"alternariose"},{"Id":76,"Text":"anthracnose"},{"Id":128,"Text":"black-rot de la vigne"},{"Id":20,"Text":"cercosporose"},{"Id":123,"Text":"cloque du pêcher"},{"Id":133,"Text":"cylindrosporiose des crucifères"},{"Id":240,"Text":"feu bactérien sur arbres fruitiers"},{"Id":151,"Text":"maladie des taches brunes sur orge"},{"Id":113,"Text":"mildiou"},{"Id":24,"Text":"mildiou de la tomate"},{"Id":44,"Text":"mildiou de la vigne"},{"Id":226,"Text":"mildiou de l'oignon"},{"Id":95,"Text":"mildiou sur pomme de terre"},{"Id":60,"Text":"nécrose du collet des crucifères"},{"Id":23,"Text":"oïdium"},{"Id":30,"Text":"oïdium de la vigne"},{"Id":185,"Text":"oïdium des arbres fruitiers"},{"Id":233,"Text":"piétin-verse"},{"Id":7,"Text":"pourriture"},{"Id":201,"Text":"pourriture grise"},{"Id":102,"Text":"rhizoctone"},{"Id":188,"Text":"rouille brune du blé"},{"Id":167,"Text":"sclérotiniose"},{"Id":187,"Text":"septoriose"},{"Id":15,"Text":"septoriose du céleri"},{"Id":131,"Text":"sharka"},{"Id":48,"Text":"tavelure"},{"Id":42,"Text":"tavelure du pommier"},{"Id":254,"Text":"virus"}],
"Id_Area":17,
"Occurences":[],
"Plants":[]}*/

/*DateEnd: "28/07/2011"
DateStart: "02/11/1945"
Id_Area: "10"
Id_Bioagressor: ""
Id_Disease: ""
Id_Plant: "78"
SearchText: ""*/
/*{"ErrorMessage":null,"ErrorStackTrace":null,"AreaName":"Centre","Bioagressors":[{"Id":338,"Text":"mouche"},{"Id":158,"Text":"mouche grise"},{"Id":136,"Text":"taupin"}],"Diseases":[{"Id":57,"Text":"flétrissement bactérien à xanthomonas des graminées"},{"Id":113,"Text":"mildiou"},{"Id":7,"Text":"pourriture"}],"Id_Area":10,"Occurences":[],"Plants":[]}*/

$app->post('/Services/Vespa.svc/GetAreaDetails', function(Request $request) use ($app) {
    // Récupération des critères de recherche
    $req = json_decode( $request->getContent(), true );
    if ( ( is_null( $req ) ) || (count($req) == 0 ) ) {
        $idPlant = $request->get('Id_Plant');
        $idBioagressor = $request->get('Id_Bioagressor');
        $idDisease = $request->get('Id_Disease');
        $idArea = $request->get('Id_Area');
        $dateStart = $request->get('DateStart');
        $dateEnd = $request->get('DateEnd');
        $searchText = $request->get('SearchText');
    } else {
        $idPlant = $req['Id_Plant'];
        $idBioagressor = $req['Id_Bioagressor'];
        $idDisease = $req['Id_Disease'];
        $idArea = $req['Id_Area'];
        $dateStart = $req['DateStart'];
        $dateEnd = $req['DateEnd'];
        $searchText = $req['SearchText'];
    }

    // Petit reformatage du motif de recherche textuel
    if ( is_null( $searchText ) || $searchText == '' ) {
        $textLike = null;
    } else {
        $textLike = "%".$searchText."%";
    }

    // Recherche du nom de zone
    if ( is_null( $idArea ) || $idArea == "" ) {
        $response['AreaName'] = null;
    } else {
        $sql = "SELECT Name FROM area WHERE id = ?";
        $res = $app['db']->fetchAssoc( $sql, array( $idArea ) );
        foreach( $res as $key=>$value )
            $response['AreaName'] = $value ;
    }

    // Récupération des plantes de la zone
    if ( ! ( is_null( $idDisease ) || $idDisease == "" ) ) {
        $sql = "SELECT DISTINCT plant.id, plant.name
                FROM area
                LEFT OUTER JOIN report
                ON area.id = report.id_area
                LEFT OUTER JOIN plant_disease
                ON plant_disease.id_report = report.id
                LEFT OUTER JOIN plant
                ON plant_disease.id_plant = plant.id
                WHERE ( ? IS NULL OR report.date > STR_TO_DATE( ? , '%d/%m/%Y' ) )
                AND ( ? IS NULL OR report.date < STR_TO_DATE( ? , '%d/%m/%Y' ) )
                AND ( plant_disease.id_disease = ? )
                AND ( report.id_area = ? )
                AND ( ? IS NULL OR report.content LIKE ? )
                ORDER BY plant.name";
        $res_plants = $app['db']->fetchAll($sql, array( $dateStart, $dateStart, $dateEnd, $dateEnd, $idDisease, $idArea, $textLike, $textLike ) );
    } else {
        $sql = "SELECT DISTINCT plant.id, plant.name
                FROM area
                LEFT OUTER JOIN report
                ON area.id = report.id_area
                LEFT OUTER JOIN plant_bioagressor
                ON plant_bioagressor.id_report = report.id
                LEFT OUTER JOIN plant
                ON plant_bioagressor.id_plant = plant.id
                WHERE ( ? IS NULL OR report.date > STR_TO_DATE( ? , '%d/%m/%Y' ) )
                AND ( ? IS NULL OR report.date < STR_TO_DATE( ? , '%d/%m/%Y' ) )
                AND ( plant_bioagressor.id_bioagressor = ? )
                AND ( report.id_area = ? )
                AND ( ? IS NULL OR report.content LIKE ? )
                ORDER BY plant.name";
        $res_plants = $app['db']->fetchAll($sql, array( $dateStart, $dateStart, $dateEnd, $dateEnd, $idBioagressor, $idArea, $textLike, $textLike ) );
    }

    // Récupération des maladies de la zone
    $sql = "SELECT DISTINCT disease.id, disease.name
            FROM area
            LEFT OUTER JOIN report
            ON area.id = report.id_area
            LEFT OUTER JOIN plant_disease
            ON plant_disease.id_report = report.id
            LEFT OUTER JOIN disease
            ON plant_disease.id_disease = disease.id
            WHERE ( ? IS NULL OR report.date > STR_TO_DATE( ? , '%d/%m/%Y' ) )
            AND ( ? IS NULL OR report.date < STR_TO_DATE( ? , '%d/%m/%Y' ) )
            AND ( plant_disease.id_plant = ? )
            AND ( report.id_area = ? )
            AND ( ? IS NULL OR report.content LIKE ? )
            ORDER BY plant.name";
    $res_diseases = $app['db']->fetchAll($sql, array( $dateStart, $dateStart, $dateEnd, $dateEnd, $idPlant, $idArea, $textLike, $textLike ) );

    // Récupération des nuisibles de la zone
    $sql = "SELECT DISTINCT bioagressor.id, bioagressor.name
            FROM area
            LEFT OUTER JOIN report
            ON area.id = report.id_area
            LEFT OUTER JOIN plant_bioagressor
            ON plant_bioagressor.id_report = report.id
            LEFT OUTER JOIN bioagressor
            ON plant_bioagressor.id_bioagressor = bioagressor.id
            WHERE ( ? IS NULL OR report.date > STR_TO_DATE( ? , '%d/%m/%Y' ) )
            AND ( ? IS NULL OR report.date < STR_TO_DATE( ? , '%d/%m/%Y' ) )
            AND ( plant_bioagressor.id_plant = ? )
            AND ( report.id_area = ? )
            AND ( ? IS NULL OR report.content LIKE ? )
            ORDER BY plant.name";
    $res_bugs = $app['db']->fetchAll($sql, array( $dateStart, $dateStart, $dateEnd, $dateEnd, $idPlant, $idArea, $textLike, $textLike ) );

    // Calcul des occurences de la zone
    if ( ! ( is_null( $idDisease ) || $idDisease == "" ) ) {
        $sql = "SELECT report.id AS Id, plant_disease.Comment AS Text, report.date AS Date
                FROM report
                LEFT OUTER JOIN plant_disease
                ON plant_disease.id_report = report.id
                WHERE ( ? IS NULL OR report.date > STR_TO_DATE( ? , '%d/%m/%Y' ) )
                AND ( ? IS NULL OR report.date < STR_TO_DATE( ? , '%d/%m/%Y' ) )
                AND ( plant_disease.id_disease = ? )
                AND ( report.id_area = ? )
                AND ( ? IS NULL OR report.content LIKE ? )
                AND ( plant_disease.id_plant = ? )
                ORDER BY report.date";
        $res_occ = $app['db']->fetchAll($sql, array( $dateStart, $dateStart, $dateEnd, $dateEnd, $idDisease, $idArea, $textLike, $textLike, $idPlant ) );
    } else {
        $sql = "SELECT report.id AS Id, plant_bioagressor.Comment AS Text, report.date AS Date
                FROM report
                LEFT OUTER JOIN plant_bioagressor
                ON plant_bioagressor.id_report = report.id
                WHERE ( ? IS NULL OR report.date > STR_TO_DATE( ? , '%d/%m/%Y' ) )
                AND ( ? IS NULL OR report.date < STR_TO_DATE( ? , '%d/%m/%Y' ) )
                AND ( plant_bioagressor.id_bioagressor = ? )
                AND ( report.id_area = ? )
                AND ( ? IS NULL OR report.content LIKE ? )
                AND ( plant_bioagressor.id_plant = ? )
                ORDER BY report.date";
        $res_occ = $app['db']->fetchAll($sql, array( $dateStart, $dateStart, $dateEnd, $dateEnd, $idBioagressor, $idArea, $textLike, $textLike, $idPlant ) );
    }
    
    $response['ErrorMessage'] = null;
    $response['ErrorStackTrace'] = null;
    $response['Id_Area'] = $idArea;
    $response['Plants'] = $res_plants;
    $response['Bioagressors'] = $res_bugs;
    $response['Diseases'] = $res_diseases;
    $response['Occurences'] = $res_occ;
    return $app->json($response);
});



/*****************************************************************************************
 *                                                                                       *
 * Requête de récupération de la liste des rapports avec les filtres en place            *
 *                                                                                       *
 *****************************************************************************************/
$app->post('/Services/Vespa.svc/GetSearchReportList', function(Request $request) use ($app) {
    // Récupération des critères de recherche
    $req = json_decode( $request->getContent(), true );
    if ( ( is_null( $req ) ) || (count($req) == 0 ) ) {
        $idPlant = $request->get('Id_Plant');
        $idBioagressor = $request->get('Id_Bioagressor');
        $idDisease = $request->get('Id_Disease');
        $dateStart = $request->get('DateStart');
        $dateEnd = $request->get('DateEnd');
        $searchText = $request->get('SearchText');
    } else {
        $idPlant = $req['Id_Plant'];
        $idBioagressor = $req['Id_Bioagressor'];
        $idDisease = $req['Id_Disease'];
        $dateStart = $req['DateStart'];
        $dateEnd = $req['DateEnd'];
        $searchText = $req['SearchText'];
    }

    // Petit reformatage du motif de recherche textuel
    if ( is_null( $searchText ) || $searchText == '' ) {
        $textLike = null;
    } else {
        $textLike = "%".$searchText."%";
    }

    // Construction de la requête en fonction des critères de recherche en paramètre
    if ( ! is_null( $idBioagressor ) ) {
        $sql = "SELECT report.id as id, report.date as date, report.datestr as datestring, report.name as name, 
                       area.name as areaname, report.id_area as id_area, YEAR(report.date) as year
                FROM report
                INNER JOIN area ON report.id_area = area.id
                INNER JOIN plant_bioagressor ON report.id = plant_bioagressor.id_report
                WHERE ( ? IS NULL OR plant_bioagressor.id_plant = ? )
                AND ( plant_bioagressor.id_bioagressor = ? )
                AND ( ? IS NULL OR report.date > STR_TO_DATE( ? , '%d/%m/%Y' ) )
                AND ( ? IS NULL OR report.date < STR_TO_DATE( ? , '%d/%m/%Y' ) )
                AND ( ? IS NULL OR report.content LIKE ? )
                GROUP BY report.id
                ORDER BY report.id";
        $res_reports = $app['db']->fetchAll($sql, array( $idPlant, $idPlant, $idBioagressor, $dateStart, $dateStart, $dateEnd, $dateEnd, $textLike, $textLike ) );
    } else if ( ! is_null( $idDisease ) ) {
        $sql = "SELECT report.id as id, report.date as date, report.datestr as datestring, report.name as name, 
                       area.name as areaname, report.id_area as id_area, YEAR(report.date) as year
                FROM report
                INNER JOIN area ON report.id_area = area.id
                INNER JOIN plant_disease ON report.id = plant_disease.id_report
                WHERE ( ? IS NULL OR plant_disease.id_plant = ? )
                AND ( plant_disease.id_disease = ? )
                AND ( ? IS NULL OR report.date > STR_TO_DATE( ? , '%d/%m/%Y' ) )
                AND ( ? IS NULL OR report.date < STR_TO_DATE( ? , '%d/%m/%Y' ) )
                AND ( ? IS NULL OR report.content LIKE ? )
                GROUP BY report.id
                ORDER BY report.id";
        $res_reports = $app['db']->fetchAll($sql, array( $idPlant, $idPlant, $idDisease, $dateStart, $dateStart, $dateEnd, $dateEnd, $textLike, $textLike ) );
    } else {
        $sql = "SELECT report.id as id, report.date as date, report.datestr as datestring, report.name as name, 
                       area.name as areaname, report.id_area as id_area, YEAR(report.date) as year
                FROM report
                INNER JOIN area ON report.id_area = area.id
                LEFT JOIN plant_bioagressor
                ON plant_bioagressor.id_plant = ? AND report.id = plant_bioagressor.id_report
                LEFT JOIN plant_disease 
                ON plant_disease.id_plant = ? AND report.id = plant_disease.id_report
                WHERE ( plant_bioagressor.id_plant = ? OR plant_disease.id_plant = ? )
                AND ( ? IS NULL OR report.date > STR_TO_DATE( ? , '%d/%m/%Y' ) )
                AND ( ? IS NULL OR report.date < STR_TO_DATE( ? , '%d/%m/%Y' ) )
                AND ( ? IS NULL OR report.content LIKE ? )
                GROUP BY report.id
                ORDER BY report.id";
        $res_reports = $app['db']->fetchAll($sql, array( $idPlant, $idPlant, $idPlant, $idPlant, $dateStart, $dateStart, $dateEnd, $dateEnd, $textLike, $textLike ) );
    }

    // Reformatage des résultats
    foreach( $res_reports as $report) {
        // Stockage des Ids pour la requête sur les années un peu plus loin
        $ids[] = (int) $report['id'];
        
        // Conversion de l'encodage
        $reports[] = array( "AreaName"=>mb_convert_encoding($report['areaname'], "UTF-8"),
                            "Date"=>mb_convert_encoding($report['date'], "UTF-8"),
                            "DateString"=>str_replace('.','/',mb_convert_encoding($report['datestring'], "UTF-8")),
                            "Id"=>(int) $report['id'],
                            "Id_Area"=>(int) $report['id_area'],
                            "Name"=>mb_convert_encoding($report['name'], "UTF-8"),
                            "Year"=>(int) $report['year'] );
    }

    // Préparation de la liste des reports ID pour la requête sur les années
    $ids = "( ".implode(",",$ids)." )";

    // Comptage des reports par année
    $sql = "SELECT YEAR(report.date) AS id, YEAR(report.Date) AS text, COUNT( report.Id ) AS count
            FROM report
            WHERE id IN ".$ids."
            GROUP BY YEAR(report.date)";
    $res_years = $app['db']->fetchAll($sql);

    // Reformatage des résultats
    foreach( $res_years as $year) {
        $years[] = array( "Id"=>(int) $year['id'],
                          "Text"=>$year['id'],
                          "Count"=>(int) $year['count'] );
    }

    // Récupération des noms des critères de recherche s'ils sont présents
    if ( is_null( $idBioagressor ) || $idBioagressor == "" ) {
        $response['BioagressorName'] = null;
    } else {
        $sql = "SELECT Name FROM bioagressor WHERE id = ?";
        $res = $app['db']->fetchAssoc( $sql, array( $idBioagressor   ) );
        foreach( $res as $key=>$value )
            $response['BioagressorName'] = $value ;
    }

    if ( is_null( $idDisease ) || $idDisease == "" ) {
        $response['DiseaseName'] = null;
    } else {
        $sql = "SELECT Name FROM disease WHERE id = ?";
        $res = $app['db']->fetchAssoc( $sql, array( $idDisease ) );
        foreach( $res as $key=>$value )
            $response['DiseaseName'] = $value ;
    }

    if ( is_null( $idPlant ) || $idPlant == "" ) {
        $response['PlantName'] = null;
    } else {
        $sql = "SELECT Name FROM plant WHERE id = ?";
        $res = $app['db']->fetchAssoc( $sql, array( $idPlant ) );
        foreach( $res as $key=>$value )
            $response['PlantName'] = $value ;
    }

    // Construction du tableau retour
    $response['ErrorMessage'] = null;
    $response['ErrorStackTrace'] = null;
    $response['DateStart'] = $dateStart ;
    $response['DateEnd'] = $dateEnd ;
    $response['Reports'] = $reports;
    $response['SearchText'] = $searchText ;
    $response['Years'] = $years;

    // Conversion de la réponse en JSON et retour
    return $app->json($response);
});

/*****************************************************************************************
 *                                                                                       *
 * SimpleUser options. See config reference below for details.                           *
 *                                                                                       *
 *****************************************************************************************/
$app['user.options'] = array(
    'templates' => array(
        'layout' => 'layout.twig',
        'register' => 'register.twig',
        'register-confirmation-sent' => 'register-confirmation-sent.twig',
        'login' => 'login.twig',
        'login-confirmation-needed' => 'login-confirmation-needed.twig',
        'forgot-password' => 'forgot-password.twig',
        'reset-password' => 'reset-password.twig',
        'view' => 'view.twig',
        'edit' => 'edit.twig',
        'list' => 'list.twig',
    ),

    // Configure the user mailer for sending password reset and email confirmation messages.
    'mailer' => array(
        'enabled' => true, // When false, email notifications are not sent (they're silently discarded).
        'fromEmail' => array(
            'address' => 'vespa@cortext.fr',
            'name' => 'Vespa',
        ),
    ),

    'emailConfirmation' => array(
        'required' => true, // Whether to require email confirmation before enabling new accounts.
        'template' => 'email/confirm-email.twig',
    ),

    'passwordReset' => array(
        'template' => 'email/reset-password.twig',
        'tokenTTL' => 86400, // How many seconds the reset token is valid for. Default: 1 day.
    ),

);

/*********************************************************************************************
 *                                                                                           *
 * Security config. See http://silex.sensiolabs.org/doc/providers/security.html for details. *
 *                                                                                           *
 ********************************************************************************************/
$app['security.firewalls'] = array(
    'login' => array(
        'pattern' => '^/user/login$',
        'anonymous' => true,
    ),
    'register' => array(
        'pattern' => '^/user/register$',
        'anonymous' => true,
    ),
    'forgot-password' => array(
        'pattern' => '^/user/forgot-password$',
        'anonymous' => true,
    ),
    'reset-password' => array(
        'pattern' => '^/user/reset-password/.*$',
        'anonymous' => true,
    ),
    'confirm-email' => array(
        'pattern' => '^/user/confirm-email/.*$',
        'anonymous' => true,
    ),
    'secured_area' => array(
        'pattern' => '^.*$',
        'anonymous' => false,
        'remember_me' => array(),
        'form' => array(
            'login_path' => '/user/login',
            'check_path' => '/user/login_check',
        ),
        'logout' => array(
            'logout_path' => '/user/logout',
        ),
        'users' => $app->share(function($app) { return $app['user.manager']; }),
    ),
);

/*********************************************************************************************
 *                                                                                           *
 * Définition de l'emplacement des templates pour utiliser ceux de Vespa.                    *
 *                                                                                           *
 ********************************************************************************************/
$app['twig.path'] = array(__DIR__.'/../views');


/*********************************************************************************************
 *                                                                                           *
 * Mailer config. See http://silex.sensiolabs.org/doc/providers/swiftmailer.html             *
 *                                                                                           *
 ********************************************************************************************/
$app['swiftmailer.options'] = array();
$app['swiftmailer.use_spool'] = false;

/*********************************************************************************************
 *                                                                                           *
 * Database config. See http://silex.sensiolabs.org/doc/providers/doctrine.html              *
 *                                                                                           *
 ********************************************************************************************/
$app['db.options'] = array(
    'driver'   => $parameters['db.options']['driver'],
    'host' => $parameters['db.options']['host'],
    'dbname' => $parameters['db.options']['dbname'],
    'user' => $parameters['db.options']['user'],
    'password' => $parameters['db.options']['password'],
    'charset' => $parameters['db.options']['charset'],
);


/*********************************************************************************************
 *                                                                                           *
 * Création de la ligne de log pour le suivi des actions utilisateurs en se placant à la fin *
 * de la génération de la route cela permet de logger la requete aussi bien que la réponse.  *
 *                                                                                           *
 ********************************************************************************************/
$app->finish(function ($request, $response) use ($app) {
    // Définition du point de log
    $ctLog['watchpoint'] = "@".basename(__FILE__).".before.".__LINE__.":";
    $ctLog['tag'] = "CORTEXT-VESPA";

    // Log de l'user id si l'utilisateur est loggé, sinon on log 0
    $token=$app['security']->getToken();
    if ( $token !== null ) {
        $userId = $token->getUser()->getId();
    } else {
        $userId = 0;
    }
    $ctLog['user'] = $userId ;

    // Log de l'id de session pour mieux suivre les parcours utilisateurs
    //$msg .= sprintf( "[session:%s] ", $app['session']->getId() );
    $ctLog['session'] = $app['session']->getId();

    $ctLog['route'] = $request->getPathInfo();

    $ctLog['parameters'] = $request->request->all();
    if ( is_null( $ctLog['parameters'] ) || count( $ctLog['parameters'] ) == 0 ) 
        $ctLog['parameters'] = $request->query->all();
    if ( is_null( $ctLog['parameters'] ) || count( $ctLog['parameters'] ) == 0 ) 
        $ctLog['parameters'] = json_decode( $request->getContent(), true );

    $ctLog['type'] = $request->getMethod();

    $ctLog['status'] = $response->getStatusCode();

    $ctLog['response'] = json_decode($response->getContent());
    if ( !$ctLog['response'] ) $ctLog['response'] = "Not JSON";

    $duration = $app['stopwatch']->stop('vespa');
    $ctLog['duration'] = $duration->getDuration();

    $ctLog['ip'] = $request->getClientIp();

    $ctLog['msg'] = "";

    // Output de la ligne de log constituée
    $app['monolog']->addInfo( "[VESPA] ".json_encode( $ctLog, JSON_UNESCAPED_SLASHES ) );
});

$app->run();
