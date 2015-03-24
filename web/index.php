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
    'monolog.logfile' => __DIR__.'/../logs/vespa.log',
    'monolog.name' => 'Vespa',
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
    //$response['ErrorMessage'] = null;
    //$response['ErrorStackTrace'] = null;
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
{"ErrorMessage":null,"ErrorStackTrace":null,"AreaName":"Bourgogne","Bioagressors":[{"Id":40,"Text":"acarien"},{"Id":321,"Text":"adventice"},{"Id":362,"Text":"altise"},{"Id":346,"Text":"altise des crucifères"},{"Id":309,"Text":"campagnol des champs"},{"Id":1,"Text":"carpocapse"},{"Id":30,"Text":"carpocapse des pommes et des poires"},{"Id":189,"Text":"cécidomyie"},{"Id":210,"Text":"charançon"},{"Id":28,"Text":"charançon des siliques de colza"},{"Id":137,"Text":"doryphore"},{"Id":235,"Text":"grosse altise du colza"},{"Id":59,"Text":"hanneton"},{"Id":3,"Text":"méligèthe"},{"Id":338,"Text":"mouche"},{"Id":74,"Text":"mouche de la betterave"},{"Id":280,"Text":"mouche de la carotte"},{"Id":27,"Text":"mouche de la cerise"},{"Id":12,"Text":"mouche de l'oignon"},{"Id":18,"Text":"mouche de l'olive"},{"Id":158,"Text":"mouche grise"},{"Id":198,"Text":"mouche méditerranéenne des fruits"},{"Id":355,"Text":"nématode"},{"Id":167,"Text":"nématode doré"},{"Id":21,"Text":"pou"},{"Id":295,"Text":"puceron"},{"Id":220,"Text":"puceron des épis de céréales"},{"Id":334,"Text":"punaise"},{"Id":272,"Text":"pyrale"},{"Id":99,"Text":"pyrale du maïs"},{"Id":282,"Text":"taupe d'europe"},{"Id":136,"Text":"taupin"},{"Id":179,"Text":"teigne du poireau"},{"Id":44,"Text":"tétranyque"},{"Id":164,"Text":"tordeuse"},{"Id":313,"Text":"tordeuse orientale du pêcher"}],"Diseases":[{"Id":36,"Text":"alternariose"},{"Id":76,"Text":"anthracnose"},{"Id":128,"Text":"black-rot de la vigne"},{"Id":20,"Text":"cercosporose"},{"Id":123,"Text":"cloque du pêcher"},{"Id":133,"Text":"cylindrosporiose des crucifères"},{"Id":240,"Text":"feu bactérien sur arbres fruitiers"},{"Id":151,"Text":"maladie des taches brunes sur orge"},{"Id":113,"Text":"mildiou"},{"Id":24,"Text":"mildiou de la tomate"},{"Id":44,"Text":"mildiou de la vigne"},{"Id":226,"Text":"mildiou de l'oignon"},{"Id":95,"Text":"mildiou sur pomme de terre"},{"Id":60,"Text":"nécrose du collet des crucifères"},{"Id":23,"Text":"oïdium"},{"Id":30,"Text":"oïdium de la vigne"},{"Id":185,"Text":"oïdium des arbres fruitiers"},{"Id":233,"Text":"piétin-verse"},{"Id":7,"Text":"pourriture"},{"Id":201,"Text":"pourriture grise"},{"Id":102,"Text":"rhizoctone"},{"Id":188,"Text":"rouille brune du blé"},{"Id":167,"Text":"sclérotiniose"},{"Id":187,"Text":"septoriose"},{"Id":15,"Text":"septoriose du céleri"},{"Id":131,"Text":"sharka"},{"Id":48,"Text":"tavelure"},{"Id":42,"Text":"tavelure du pommier"},{"Id":254,"Text":"virus"}],"Id_Area":17,"Occurences":[],"Plants":[]}*/

/* Requete
{Id_Plant: "78", Id_Bioagressor: "", Id_Disease: "", DateStart: "02/11/1945", DateEnd: "28/07/2011",…}
DateEnd: "28/07/2011"
DateStart: "02/11/1945"
Id_Area: "17"
Id_Bioagressor: ""
Id_Disease: ""
Id_Plant: "78"
SearchText: ""
*/
$app->post('/Services/Vespa.svc/GetAreaDetails', function(Request $request) use ($app) {
    $response['ACompleter'] = "";
    return $app->json($response);
});



/*****************************************************************************************
 *                                                                                       *
 * Requête de récupération de la liste des rapports avec les filtres en place            *
 *                                                                                       *
 *****************************************************************************************/
/*

Renvoi:
{"ErrorMessage":null,"ErrorStackTrace":null,
"BioagressorName":null,
"DateEnd":"28\/07\/2011",
"DateStart":"02\/11\/1945",
"DiseaseName":null,
"PlantName":"pomme de terre",
"Reports":[{"AreaName":"Bourgogne","Date":"\/Date(-423453600000+0200)\/","DateString":"01\/08\/1956","Id":1430,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1966_019","Year":1956},
{"AreaName":"Midi-Pyrénées","Date":"\/Date(-260589600000+0200)\/","DateString":"29\/09\/1961","Id":2194,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1973_013","Year":1961},
{"AreaName":"Bourgogne","Date":"\/Date(-207885600000+0200)\/","DateString":"01\/06\/1963","Id":1328,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1963_015","Year":1963},
{"AreaName":"Bourgogne","Date":"\/Date(-207885600000+0200)\/","DateString":"01\/06\/1963","Id":1329,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1963_016","Year":1963},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-207885600000+0200)\/","DateString":"01\/06\/1963","Id":2049,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1968_013","Year":1963},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-205898400000+0200)\/","DateString":"24\/06\/1963","Id":1911,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1963_001","Year":1963},{"AreaName":"Bourgogne","Date":"\/Date(-205293600000+0200)\/","DateString":"01\/07\/1963","Id":1331,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1963_018","Year":1963},{"AreaName":"Bourgogne","Date":"\/Date(-205293600000+0200)\/","DateString":"01\/07\/1963","Id":1333,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1963_020","Year":1963},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-205293600000+0200)\/","DateString":"01\/07\/1963","Id":1914,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1963_004","Year":1963},{"AreaName":"Bourgogne","Date":"\/Date(-192070800000+0100)\/","DateString":"01\/12\/1963","Id":1343,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1963_030","Year":1963},{"AreaName":"Bourgogne","Date":"\/Date(-178941600000+0200)\/","DateString":"01\/05\/1964","Id":1357,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1964_014","Year":1964},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-178941600000+0200)\/","DateString":"01\/05\/1964","Id":1937,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1964_012","Year":1964},{"AreaName":"Bourgogne","Date":"\/Date(-176263200000+0200)\/","DateString":"01\/06\/1964","Id":1359,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1964_016","Year":1964},{"AreaName":"Bourgogne","Date":"\/Date(-168314400000+0200)\/","DateString":"01\/09\/1964","Id":1367,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1964_024","Year":1964},{"AreaName":"Bourgogne","Date":"\/Date(-157770000000+0100)\/","DateString":"01\/01\/1965","Id":1377,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1965_002","Year":1965},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-157770000000+0100)\/","DateString":"01\/01\/1965","Id":1956,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1965_002","Year":1965},{"AreaName":"Bourgogne","Date":"\/Date(-147405600000+0200)\/","DateString":"01\/05\/1965","Id":1326,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1963_013","Year":1965},{"AreaName":"Bourgogne","Date":"\/Date(-147405600000+0200)\/","DateString":"01\/05\/1965","Id":1394,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1965_019","Year":1965},{"AreaName":"Bourgogne","Date":"\/Date(-147405600000+0200)\/","DateString":"01\/05\/1965","Id":1395,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1965_020","Year":1965},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-144727200000+0200)\/","DateString":"01\/06\/1965","Id":1971,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1965_017","Year":1965},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-144727200000+0200)\/","DateString":"01\/06\/1965","Id":2026,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1967_014","Year":1965},{"AreaName":"Bourgogne","Date":"\/Date(-139456800000+0200)\/","DateString":"01\/08\/1965","Id":1335,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1963_022","Year":1965},{"AreaName":"Bourgogne","Date":"\/Date(-126234000000+0100)\/","DateString":"01\/01\/1966","Id":1414,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1966_003","Year":1966},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-126234000000+0100)\/","DateString":"01\/01\/1966","Id":1985,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1966_002","Year":1966},{"AreaName":"Bourgogne","Date":"\/Date(-113191200000+0200)\/","DateString":"01\/06\/1966","Id":1425,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1966_014","Year":1966},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-113191200000+0200)\/","DateString":"01\/06\/1966","Id":1998,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1966_015","Year":1966},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-112759200000+0200)\/","DateString":"06\/06\/1966","Id":1999,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1966_016","Year":1966},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-111981600000+0200)\/","DateString":"15\/06\/1966","Id":2000,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1966_017","Year":1966},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-111290400000+0200)\/","DateString":"23\/06\/1966","Id":2001,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1966_018","Year":1966},{"AreaName":"Bourgogne","Date":"\/Date(-105242400000+0200)\/","DateString":"01\/09\/1966","Id":1433,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1966_022","Year":1966},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-105242400000+0200)\/","DateString":"01\/09\/1966","Id":2008,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1966_025","Year":1966},{"AreaName":"Bourgogne","Date":"\/Date(-94698000000+0100)\/","DateString":"01\/01\/1967","Id":1439,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1967_002","Year":1967},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-94698000000+0100)\/","DateString":"01\/01\/1967","Id":2014,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1967_002","Year":1967},{"AreaName":"Bourgogne","Date":"\/Date(-86925600000+0200)\/","DateString":"01\/04\/1967","Id":1449,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1967_012","Year":1967},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-86925600000+0200)\/","DateString":"01\/04\/1967","Id":2028,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1967_016","Year":1967},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-76384800000+0200)\/","DateString":"01\/08\/1967","Id":2031,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1967_019","Year":1967},{"AreaName":"Bourgogne","Date":"\/Date(-63162000000+0100)\/","DateString":"01\/01\/1968","Id":1478,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1968_003","Year":1968},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-63162000000+0100)\/","DateString":"01\/01\/1968","Id":2038,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1968_002","Year":1968},{"AreaName":"Bourgogne","Date":"\/Date(-50032800000+0200)\/","DateString":"01\/06\/1968","Id":1495,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1968_020","Year":1968},{"AreaName":"Bourgogne","Date":"\/Date(-50032800000+0200)\/","DateString":"01\/06\/1968","Id":1497,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1968_022","Year":1968},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-48564000000+0200)\/","DateString":"18\/06\/1968","Id":2050,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1968_014","Year":1968},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-47959200000+0200)\/","DateString":"25\/06\/1968","Id":2048,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1968_012","Year":1968},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-47440800000+0200)\/","DateString":"01\/07\/1968","Id":2052,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1968_016","Year":1968},{"AreaName":"Bourgogne","Date":"\/Date(-44762400000+0200)\/","DateString":"01\/08\/1968","Id":1502,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1968_027","Year":1968},{"AreaName":"Bourgogne","Date":"\/Date(-39492000000+0200)\/","DateString":"01\/10\/1968","Id":1506,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1968_031","Year":1968},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-39492000000+0200)\/","DateString":"01\/10\/1968","Id":2059,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1968_023","Year":1968},{"AreaName":"Bourgogne","Date":"\/Date(-31539600000+0100)\/","DateString":"01\/01\/1969","Id":1512,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1969_002","Year":1969},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-31539600000+0100)\/","DateString":"01\/01\/1969","Id":2063,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1969_002","Year":1969},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-21175200000+0200)\/","DateString":"01\/05\/1969","Id":2074,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1969_013","Year":1969},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-18496800000+0200)\/","DateString":"01\/06\/1969","Id":2075,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1969_014","Year":1969},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-18496800000+0200)\/","DateString":"01\/06\/1969","Id":2076,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1969_015","Year":1969},{"AreaName":"Bourgogne","Date":"\/Date(-17719200000+0200)\/","DateString":"10\/06\/1969","Id":1528,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1969_018","Year":1969},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-15904800000+0200)\/","DateString":"01\/07\/1969","Id":2077,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1969_016","Year":1969},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-15904800000+0200)\/","DateString":"01\/07\/1969","Id":2078,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1969_017","Year":1969},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-15904800000+0200)\/","DateString":"01\/07\/1969","Id":2080,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1969_019","Year":1969},{"AreaName":"Midi-Pyrénées","Date":"\/Date(-13226400000+0200)\/","DateString":"01\/08\/1969","Id":2082,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1969_021","Year":1969},{"AreaName":"Bourgogne","Date":"\/Date(6217200000+0100)\/","DateString":"14\/03\/1970","Id":1550,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1970_006","Year":1970},{"AreaName":"Midi-Pyrénées","Date":"\/Date(6217200000+0100)\/","DateString":"14\/03\/1970","Id":2092,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1970_005","Year":1970},{"AreaName":"Midi-Pyrénées","Date":"\/Date(13039200000+0200)\/","DateString":"01\/06\/1970","Id":2105,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1970_018","Year":1970},{"AreaName":"Midi-Pyrénées","Date":"\/Date(13039200000+0200)\/","DateString":"01\/06\/1970","Id":2106,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1970_019","Year":1970},{"AreaName":"Midi-Pyrénées","Date":"\/Date(13039200000+0200)\/","DateString":"01\/06\/1970","Id":2107,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1970_020","Year":1970},{"AreaName":"Midi-Pyrénées","Date":"\/Date(13039200000+0200)\/","DateString":"01\/06\/1970","Id":2109,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1970_022","Year":1970},{"AreaName":"Midi-Pyrénées","Date":"\/Date(18309600000+0200)\/","DateString":"01\/08\/1970","Id":2114,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1970_027","Year":1970},{"AreaName":"Bourgogne","Date":"\/Date(31532400000+0100)\/","DateString":"01\/01\/1971","Id":1585,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1971_007","Year":1971},{"AreaName":"Midi-Pyrénées","Date":"\/Date(38790000000+0100)\/","DateString":"26\/03\/1971","Id":2125,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1971_005","Year":1971},{"AreaName":"Midi-Pyrénées","Date":"\/Date(41896800000+0200)\/","DateString":"01\/05\/1971","Id":2135,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1971_015","Year":1971},{"AreaName":"Bourgogne","Date":"\/Date(44575200000+0200)\/","DateString":"01\/06\/1971","Id":1599,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1971_021","Year":1971},{"AreaName":"Midi-Pyrénées","Date":"\/Date(44575200000+0200)\/","DateString":"01\/06\/1971","Id":2139,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1971_019","Year":1971},{"AreaName":"Midi-Pyrénées","Date":"\/Date(47167200000+0200)\/","DateString":"01\/07\/1971","Id":2143,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1971_023","Year":1971},{"AreaName":"Midi-Pyrénées","Date":"\/Date(47167200000+0200)\/","DateString":"01\/07\/1971","Id":2144,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1971_024","Year":1971},{"AreaName":"Bourgogne","Date":"\/Date(71618400000+0200)\/","DateString":"09\/04\/1972","Id":1620,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1972_009","Year":1972},{"AreaName":"Midi-Pyrénées","Date":"\/Date(71618400000+0200)\/","DateString":"09\/04\/1972","Id":2162,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1972_008","Year":1972},{"AreaName":"Bourgogne","Date":"\/Date(76197600000+0200)\/","DateString":"01\/06\/1972","Id":1629,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1972_018","Year":1972},{"AreaName":"Midi-Pyrénées","Date":"\/Date(76197600000+0200)\/","DateString":"01\/06\/1972","Id":2169,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1972_015","Year":1972},{"AreaName":"Midi-Pyrénées","Date":"\/Date(78789600000+0200)\/","DateString":"01\/07\/1972","Id":2171,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1972_017","Year":1972},{"AreaName":"Bourgogne","Date":"\/Date(81468000000+0200)\/","DateString":"01\/08\/1972","Id":1635,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1972_024","Year":1972},{"AreaName":"Midi-Pyrénées","Date":"\/Date(81468000000+0200)\/","DateString":"01\/08\/1972","Id":2177,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1972_023","Year":1972},{"AreaName":"Bourgogne","Date":"\/Date(99788400000+0100)\/","DateString":"01\/03\/1973","Id":1647,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1973_004","Year":1973},{"AreaName":"Midi-Pyrénées","Date":"\/Date(99788400000+0100)\/","DateString":"01\/03\/1973","Id":2185,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1973_004","Year":1973},{"AreaName":"Midi-Pyrénées","Date":"\/Date(102463200000+0200)\/","DateString":"01\/04\/1973","Id":2187,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1973_006","Year":1973},{"AreaName":"Midi-Pyrénées","Date":"\/Date(107733600000+0200)\/","DateString":"01\/06\/1973","Id":2193,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1973_012","Year":1973},{"AreaName":"Bourgogne","Date":"\/Date(109807200000+0200)\/","DateString":"25\/06\/1973","Id":1659,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1973_016","Year":1973},{"AreaName":"Midi-Pyrénées","Date":"\/Date(110325600000+0200)\/","DateString":"01\/07\/1973","Id":2199,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1973_018","Year":1973},{"AreaName":"Midi-Pyrénées","Date":"\/Date(110325600000+0200)\/","DateString":"01\/07\/1973","Id":2329,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1978_015","Year":1973},{"AreaName":"Bourgogne","Date":"\/Date(113522400000+0200)\/","DateString":"07\/08\/1973","Id":1666,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1973_023","Year":1973},{"AreaName":"Midi-Pyrénées","Date":"\/Date(131324400000+0100)\/","DateString":"01\/03\/1974","Id":2215,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1974_007","Year":1974},{"AreaName":"Bourgogne","Date":"\/Date(131756400000+0100)\/","DateString":"06\/03\/1974","Id":1674,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1974_004","Year":1974},{"AreaName":"Bourgogne","Date":"\/Date(132274800000+0100)\/","DateString":"12\/03\/1974","Id":1675,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1974_005","Year":1974},{"AreaName":"Midi-Pyrénées","Date":"\/Date(138319200000+0200)\/","DateString":"21\/05\/1974","Id":2222,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1974_014","Year":1974},{"AreaName":"Midi-Pyrénées","Date":"\/Date(144540000000+0200)\/","DateString":"01\/08\/1974","Id":2230,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1974_022","Year":1974},{"AreaName":"Midi-Pyrénées","Date":"\/Date(144540000000+0200)\/","DateString":"01\/08\/1974","Id":2231,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1974_023","Year":1974},{"AreaName":"Midi-Pyrénées","Date":"\/Date(168127200000+0200)\/","DateString":"01\/05\/1975","Id":2248,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1975_011","Year":1975},{"AreaName":"Bourgogne","Date":"\/Date(170805600000+0200)\/","DateString":"01\/06\/1975","Id":1709,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1975_015","Year":1975},{"AreaName":"Midi-Pyrénées","Date":"\/Date(174261600000+0200)\/","DateString":"11\/07\/1975","Id":2255,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1975_018","Year":1975},{"AreaName":"Midi-Pyrénées","Date":"\/Date(202082400000+0200)\/","DateString":"28\/05\/1976","Id":2276,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1976_012","Year":1976},{"AreaName":"Midi-Pyrénées","Date":"\/Date(217638000000+0100)\/","DateString":"24\/11\/1976","Id":2288,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1976_024","Year":1976},{"AreaName":"Midi-Pyrénées","Date":"\/Date(223599600000+0100)\/","DateString":"01\/02\/1977","Id":2291,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1977_002","Year":1977},{"AreaName":"Midi-Pyrénées","Date":"\/Date(233964000000+0200)\/","DateString":"01\/06\/1977","Id":2302,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1977_013","Year":1977},{"AreaName":"Midi-Pyrénées","Date":"\/Date(233964000000+0200)\/","DateString":"01\/06\/1977","Id":2304,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1977_015","Year":1977},{"AreaName":"Midi-Pyrénées","Date":"\/Date(235087200000+0200)\/","DateString":"14\/06\/1977","Id":2303,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1977_014","Year":1977},{"AreaName":"Midi-Pyrénées","Date":"\/Date(236556000000+0200)\/","DateString":"01\/07\/1977","Id":2305,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1977_016","Year":1977},{"AreaName":"Midi-Pyrénées","Date":"\/Date(236556000000+0200)\/","DateString":"01\/07\/1977","Id":2306,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1977_017","Year":1977},{"AreaName":"Midi-Pyrénées","Date":"\/Date(236556000000+0200)\/","DateString":"01\/07\/1977","Id":2307,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1977_018","Year":1977},{"AreaName":"Bourgogne","Date":"\/Date(239234400000+0200)\/","DateString":"01\/08\/1977","Id":1774,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1977_024","Year":1977},{"AreaName":"Bourgogne","Date":"\/Date(259369200000+0100)\/","DateString":"22\/03\/1978","Id":1789,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1978_007","Year":1978},{"AreaName":"Midi-Pyrénées","Date":"\/Date(262821600000+0200)\/","DateString":"01\/05\/1978","Id":2325,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1978_011","Year":1978},{"AreaName":"Midi-Pyrénées","Date":"\/Date(265500000000+0200)\/","DateString":"01\/06\/1978","Id":2328,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1978_014","Year":1978},{"AreaName":"Bourgogne","Date":"\/Date(265586400000+0200)\/","DateString":"02\/06\/1978","Id":1797,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1978_015","Year":1978},{"AreaName":"Bourgogne","Date":"\/Date(267400800000+0200)\/","DateString":"23\/06\/1978","Id":1799,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1978_017","Year":1978},{"AreaName":"Bourgogne","Date":"\/Date(268351200000+0200)\/","DateString":"04\/07\/1978","Id":1800,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1978_018","Year":1978},{"AreaName":"Midi-Pyrénées","Date":"\/Date(269647200000+0200)\/","DateString":"19\/07\/1978","Id":2330,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1978_016","Year":1978},{"AreaName":"Bourgogne","Date":"\/Date(269733600000+0200)\/","DateString":"20\/07\/1978","Id":1802,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1978_020","Year":1978},{"AreaName":"Bourgogne","Date":"\/Date(270252000000+0200)\/","DateString":"26\/07\/1978","Id":1803,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1978_021","Year":1978},{"AreaName":"Bourgogne","Date":"\/Date(272152800000+0200)\/","DateString":"17\/08\/1978","Id":1805,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1978_023","Year":1978},{"AreaName":"Midi-Pyrénées","Date":"\/Date(272152800000+0200)\/","DateString":"17\/08\/1978","Id":2331,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1978_017","Year":1978},{"AreaName":"Bourgogne","Date":"\/Date(280710000000+0100)\/","DateString":"24\/11\/1978","Id":1811,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1978_029","Year":1978},{"AreaName":"Bourgogne","Date":"\/Date(297986400000+0200)\/","DateString":"12\/06\/1979","Id":1828,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1979_017","Year":1979},{"AreaName":"Bourgogne","Date":"\/Date(298591200000+0200)\/","DateString":"19\/06\/1979","Id":1829,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1979_018","Year":1979},{"AreaName":"Midi-Pyrénées","Date":"\/Date(298677600000+0200)\/","DateString":"20\/06\/1979","Id":2350,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1979_013","Year":1979},{"AreaName":"Bourgogne","Date":"\/Date(299887200000+0200)\/","DateString":"04\/07\/1979","Id":1831,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1979_020","Year":1979},{"AreaName":"Bourgogne","Date":"\/Date(304034400000+0200)\/","DateString":"21\/08\/1979","Id":1837,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1979_026","Year":1979},{"AreaName":"Bourgogne","Date":"\/Date(322354800000+0100)\/","DateString":"20\/03\/1980","Id":1850,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1980_006","Year":1980},{"AreaName":"Midi-Pyrénées","Date":"\/Date(328917600000+0200)\/","DateString":"04\/06\/1980","Id":2379,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1980_018","Year":1980},{"AreaName":"Bourgogne","Date":"\/Date(329608800000+0200)\/","DateString":"12\/06\/1980","Id":1861,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1980_017","Year":1980},{"AreaName":"Bourgogne","Date":"\/Date(330300000000+0200)\/","DateString":"20\/06\/1980","Id":1862,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1980_018","Year":1980},{"AreaName":"Bourgogne","Date":"\/Date(331855200000+0200)\/","DateString":"08\/07\/1980","Id":1864,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1980_020","Year":1980},{"AreaName":"Midi-Pyrénées","Date":"\/Date(331941600000+0200)\/","DateString":"09\/07\/1980","Id":2383,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1980_022","Year":1980},{"AreaName":"Bourgogne","Date":"\/Date(344818800000+0100)\/","DateString":"05\/12\/1980","Id":1875,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1980_031","Year":1980},{"AreaName":"Bourgogne","Date":"\/Date(360194400000+0200)\/","DateString":"01\/06\/1981","Id":1892,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1981_017","Year":1981},{"AreaName":"Bourgogne","Date":"\/Date(360194400000+0200)\/","DateString":"01\/06\/1981","Id":1894,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1981_019","Year":1981},{"AreaName":"Midi-Pyrénées","Date":"\/Date(360194400000+0200)\/","DateString":"01\/06\/1981","Id":2410,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1981_017","Year":1981},{"AreaName":"Bourgogne","Date":"\/Date(360453600000+0200)\/","DateString":"04\/06\/1981","Id":1891,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1981_016","Year":1981},{"AreaName":"Bourgogne","Date":"\/Date(362613600000+0200)\/","DateString":"29\/06\/1981","Id":1896,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1981_021","Year":1981},{"AreaName":"Bourgogne","Date":"\/Date(376095600000+0100)\/","DateString":"02\/12\/1981","Id":1910,"Id_Area":17,"Name":"AA_TC_Bourgogne_Franche_comte_1981_035","Year":1981},{"AreaName":"Bourgogne","Date":"\/Date(391903200000+0200)\/","DateString":"03\/06\/1982","Id":556,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1982_013","Year":1982},{"AreaName":"Bourgogne","Date":"\/Date(392076000000+0200)\/","DateString":"05\/06\/1982","Id":557,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1982_014","Year":1982},{"AreaName":"Bourgogne","Date":"\/Date(393112800000+0200)\/","DateString":"17\/06\/1982","Id":558,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1982_015","Year":1982},{"AreaName":"Bourgogne","Date":"\/Date(394149600000+0200)\/","DateString":"29\/06\/1982","Id":559,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1982_016","Year":1982},{"AreaName":"Bourgogne","Date":"\/Date(394927200000+0200)\/","DateString":"08\/07\/1982","Id":560,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1982_017","Year":1982},{"AreaName":"Midi-Pyrénées","Date":"\/Date(419464800000+0200)\/","DateString":"18\/04\/1983","Id":1113,"Id_Area":9,"Name":"AA_GC_Midi_Pyrenees_1983_005","Year":1983},{"AreaName":"Bourgogne","Date":"\/Date(422748000000+0200)\/","DateString":"26\/05\/1983","Id":580,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1983_011","Year":1983},{"AreaName":"Bourgogne","Date":"\/Date(440204400000+0100)\/","DateString":"14\/12\/1983","Id":597,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1983_028","Year":1983},{"AreaName":"Bourgogne","Date":"\/Date(455493600000+0200)\/","DateString":"08\/06\/1984","Id":611,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1984_014","Year":1984},{"AreaName":"Bourgogne","Date":"\/Date(456530400000+0200)\/","DateString":"20\/06\/1984","Id":613,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1984_016","Year":1984},{"AreaName":"Bourgogne","Date":"\/Date(457221600000+0200)\/","DateString":"28\/06\/1984","Id":614,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1984_017","Year":1984},{"AreaName":"Bourgogne","Date":"\/Date(486338400000+0200)\/","DateString":"31\/05\/1985","Id":638,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1985_014","Year":1985},{"AreaName":"Bourgogne","Date":"\/Date(517960800000+0200)\/","DateString":"01\/06\/1986","Id":663,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1986_016","Year":1986},{"AreaName":"Bourgogne","Date":"\/Date(518220000000+0200)\/","DateString":"04\/06\/1986","Id":662,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1986_015","Year":1986},{"AreaName":"Bourgogne","Date":"\/Date(519516000000+0200)\/","DateString":"19\/06\/1986","Id":664,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1986_017","Year":1986},{"AreaName":"Bourgogne","Date":"\/Date(520034400000+0200)\/","DateString":"25\/06\/1986","Id":665,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1986_018","Year":1986},{"AreaName":"Bourgogne","Date":"\/Date(521244000000+0200)\/","DateString":"09\/07\/1986","Id":667,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1986_020","Year":1986},{"AreaName":"Bourgogne","Date":"\/Date(548978400000+0200)\/","DateString":"26\/05\/1987","Id":689,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1987_015","Year":1987},{"AreaName":"Bourgogne","Date":"\/Date(550447200000+0200)\/","DateString":"12\/06\/1987","Id":691,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1987_017","Year":1987},{"AreaName":"Bourgogne","Date":"\/Date(552088800000+0200)\/","DateString":"01\/07\/1987","Id":694,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1987_020","Year":1987},{"AreaName":"Bourgogne","Date":"\/Date(579218400000+0200)\/","DateString":"10\/05\/1988","Id":713,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1988_011","Year":1988},{"AreaName":"Bourgogne","Date":"\/Date(579909600000+0200)\/","DateString":"18\/05\/1988","Id":714,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1988_012","Year":1988},{"AreaName":"Bourgogne","Date":"\/Date(581119200000+0200)\/","DateString":"01\/06\/1988","Id":716,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1988_014","Year":1988},{"AreaName":"Bourgogne","Date":"\/Date(582242400000+0200)\/","DateString":"14\/06\/1988","Id":717,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1988_015","Year":1988},{"AreaName":"Bourgogne","Date":"\/Date(670114800000+0100)\/","DateString":"28\/03\/1991","Id":788,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1991_005","Year":1991},{"AreaName":"Midi-Pyrénées","Date":"\/Date(670802400000+0200)\/","DateString":"05\/04\/1991","Id":1253,"Id_Area":9,"Name":"AA_GC_Midi_Pyrenees_1991_006","Year":1991},{"AreaName":"Bourgogne","Date":"\/Date(819414000000+0100)\/","DateString":"20\/12\/1995","Id":915,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1995_027","Year":1995},{"AreaName":"Bourgogne","Date":"\/Date(898639200000+0200)\/","DateString":"24\/06\/1998","Id":994,"Id_Area":17,"Name":"AA_GC_Bourgogne_Franche_comte_1998_021","Year":1998},{"AreaName":"Centre","Date":"\/Date(1077663600000+0100)\/","DateString":"25\/02\/2004","Id":84,"Id_Area":10,"Name":"AvisGC 612","Year":2004},{"AreaName":"Centre","Date":"\/Date(1091052000000+0200)\/","DateString":"29\/07\/2004","Id":489,"Id_Area":10,"Name":"GC Centre 29.07.04","Year":2004},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1091484000000+0200)\/","DateString":"03\/08\/2004","Id":103,"Id_Area":1,"Name":"AvisGC 633","Year":2004},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1092175200000+0200)\/","DateString":"11\/08\/2004","Id":104,"Id_Area":1,"Name":"AvisGC 634","Year":2004},{"AreaName":"Centre","Date":"\/Date(1092261600000+0200)\/","DateString":"12\/08\/2004","Id":424,"Id_Area":10,"Name":"GC Centre 12.08.04","Year":2004},{"AreaName":"Midi-Pyrénées","Date":"\/Date(1109545200000+0100)\/","DateString":"28\/02\/2005","Id":511,"Id_Area":9,"Name":"GC-05-05","Year":2005},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1123624800000+0200)\/","DateString":"10\/08\/2005","Id":132,"Id_Area":1,"Name":"AvisGC 666","Year":2005},{"AreaName":"Centre","Date":"\/Date(1124229600000+0200)\/","DateString":"17\/08\/2005","Id":443,"Id_Area":10,"Name":"GC Centre 17.08.05 suite","Year":2005},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1155074400000+0200)\/","DateString":"09\/08\/2006","Id":163,"Id_Area":1,"Name":"AvisGC 697","Year":2006},{"AreaName":"Centre","Date":"\/Date(1155679200000+0200)\/","DateString":"16\/08\/2006","Id":437,"Id_Area":10,"Name":"GC Centre 16.08.06 suite","Year":2006},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1303941600000+0200)\/","DateString":"28\/04\/2011","Id":49,"Id_Area":1,"Name":"5-_BSV_Grandes_cultures_S17_cle0fb7bf","Year":2011},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1304546400000+0200)\/","DateString":"05\/05\/2011","Id":52,"Id_Area":1,"Name":"6-_BSV_Grandes_cultures_S18_cle03a9a9","Year":2011},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1305151200000+0200)\/","DateString":"12\/05\/2011","Id":53,"Id_Area":1,"Name":"7-_BSV_Grandes_cultures_S19_cle0fc223","Year":2011},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1305756000000+0200)\/","DateString":"19\/05\/2011","Id":54,"Id_Area":1,"Name":"8-_BSV_Grandes_cultures_S20_cle01513c","Year":2011},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1306360800000+0200)\/","DateString":"26\/05\/2011","Id":56,"Id_Area":1,"Name":"9-_BSV_Grandes_cultures_S21_cle077b11","Year":2011},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1306879200000+0200)\/","DateString":"01\/06\/2011","Id":4,"Id_Area":1,"Name":"10_-_BSV_Grandes_cultures_S22_cle88b6bc","Year":2011},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1307570400000+0200)\/","DateString":"09\/06\/2011","Id":6,"Id_Area":1,"Name":"11_-_BSV_Grandes_cultures_S23_cle8c46a1","Year":2011},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1308175200000+0200)\/","DateString":"16\/06\/2011","Id":9,"Id_Area":1,"Name":"12_-_BSV_Grandes_cultures_S24_cle87ff4d","Year":2011},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1308780000000+0200)\/","DateString":"23\/06\/2011","Id":10,"Id_Area":1,"Name":"13_-_BSV_Grandes_cultures_S25_cle8d6e13","Year":2011},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1309384800000+0200)\/","DateString":"30\/06\/2011","Id":11,"Id_Area":1,"Name":"14_-_BSV_Grandes_cultures_S26_cle8b1d1b","Year":2011},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1309989600000+0200)\/","DateString":"07\/07\/2011","Id":13,"Id_Area":1,"Name":"15_-_BSV_Grandes_cultures_S27_cle86e12a","Year":2011},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1310508000000+0200)\/","DateString":"13\/07\/2011","Id":16,"Id_Area":1,"Name":"16_-_BSV_Grandes_cultures_S28_cle824f33","Year":2011},{"AreaName":"Champagne-Ardenne","Date":"\/Date(1311199200000+0200)\/","DateString":"21\/07\/2011","Id":17,"Id_Area":1,"Name":"17_-_BSV_Grandes_cultures_S29_cle8657c3","Year":2011}],

"SearchText":"",
"Years":[{"Id":1956,"Text":"1956","Count":1},{"Id":1961,"Text":"1961","Count":1},{"Id":1963,"Text":"1963","Count":8},{"Id":1964,"Text":"1964","Count":4},{"Id":1965,"Text":"1965","Count":8},{"Id":1966,"Text":"1966","Count":9},{"Id":1967,"Text":"1967","Count":5},{"Id":1968,"Text":"1968","Count":10},{"Id":1969,"Text":"1969","Count":10},{"Id":1970,"Text":"1970","Count":7},{"Id":1971,"Text":"1971","Count":7},{"Id":1972,"Text":"1972","Count":7},{"Id":1973,"Text":"1973","Count":8},{"Id":1974,"Text":"1974","Count":6},{"Id":1975,"Text":"1975","Count":3},{"Id":1976,"Text":"1976","Count":2},{"Id":1977,"Text":"1977","Count":8},{"Id":1978,"Text":"1978","Count":12},{"Id":1979,"Text":"1979","Count":5},{"Id":1980,"Text":"1980","Count":7},{"Id":1981,"Text":"1981","Count":6},{"Id":1982,"Text":"1982","Count":5},{"Id":1983,"Text":"1983","Count":3},{"Id":1984,"Text":"1984","Count":3},{"Id":1985,"Text":"1985","Count":1},{"Id":1986,"Text":"1986","Count":5},{"Id":1987,"Text":"1987","Count":3},{"Id":1988,"Text":"1988","Count":4},{"Id":1991,"Text":"1991","Count":2},{"Id":1995,"Text":"1995","Count":1},{"Id":1998,"Text":"1998","Count":1},{"Id":2004,"Text":"2004","Count":5},{"Id":2005,"Text":"2005","Count":3},{"Id":2006,"Text":"2006","Count":2},{"Id":2011,"Text":"2011","Count":13}]}
*/
/* Exemple de requete
{Id_Plant: "78", Id_Bioagressor: "", Id_Disease: "", DateStart: "02/11/1945", DateEnd: "28/07/2011",…}
DateEnd: "28/07/2011"
DateStart: "02/11/1945"
Id_Bioagressor: ""
Id_Disease: ""
Id_Plant: "78"  ==> Pomme de terre
SearchText: "" */

$app->post('/Services/Vespa.svc/GetSearchReportList', function(Request $request) use ($app) {
    // Récupération du filtre text
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
    
    // Construction de la requête
    //if ( ! is_null( $idBioagressor ) ) {
    $sql = "SELECT report.id as id, report.date as date, report.datestr as datestring, report.name as name, 
                   area.name as areaname, report.id_area as id_area, YEAR(report.date) as year
            FROM report
            LEFT JOIN area
            ON report.id_area = area.id
            WHERE report.date IS NOT NULL
            ORDER BY report.date
            LIMIT 0,10";
    $res_reports = $app['db']->fetchAll($sql);

    $sql = "SELECT YEAR(report.date) as id, YEAR(report.Date) as text, COUNT( report.Id ) as count
            FROM report
            WHERE id IN ( SELECT report.id FROM report WHERE report.date IS NOT NULL )
            GROUP BY YEAR(report.date)
            LIMIT 30,10";
    $res_years = $app['db']->fetchAll($sql);

/*{"AreaName":"Midi-Pyrénées","Date":"\/Date(-260589600000+0200)\/","DateString":"29\/09\/1961","Id":2194,"Id_Area":9,"Name":"AA_TC_Midi_Pyrenees_1973_013","Year":1961},*/

    // Reformatage des résultats
    foreach( $res_reports as $report) {
        $reports[] = array( "AreaName"=>mb_convert_encoding($report['areaname'], "UTF-8"),
                            "Date"=>mb_convert_encoding($report['date'], "UTF-8"),
                            "DateString"=>str_replace('.','/',mb_convert_encoding($report['datestring'], "UTF-8")),
                            "Id"=>(int) $report['id'],
                            "Id_Area"=>(int) $report['id_area'],
                            "Name"=>mb_convert_encoding($report['name'], "UTF-8"),
                            "Year"=>(int) $report['year'] );
    }

/*{"Id":1965,"Text":"1965","Count":8}*/
    foreach( $res_years as $year) {
        $years[] = array( "Id"=>(int) $year['id'],
                          "Text"=>$year['id'],
                          "Count"=>(int) $year['count'] );
    }

    // Récupération des noms si nécessaire
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

// SimpleUser options. See config reference below for details.
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

// Security config. See http://silex.sensiolabs.org/doc/providers/security.html for details.
$app['security.firewalls'] = array(
    'login' => array(
        'pattern' => '^/user/login$',
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

// Use Vespa templates
$app['twig.path'] = array(__DIR__.'/../views');

// Mailer config. See http://silex.sensiolabs.org/doc/providers/swiftmailer.html
$app['swiftmailer.options'] = array();

// Database config. See http://silex.sensiolabs.org/doc/providers/doctrine.html
$app['db.options'] = array(
    'driver'   => 'pdo_mysql',
    'host' => 'localhost',
    'dbname' => 'vespa',
    'user' => 'vespa',
    'password' => '',
    'charset' => 'utf8',
);

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

    $ctLog['msg'] = "Ici je mets ce que je veux";

    // Output de la ligne de log constituée
    $app['monolog']->addInfo( "[VESPA] ".json_encode( $ctLog, JSON_UNESCAPED_SLASHES ) );
});

$app->run();
