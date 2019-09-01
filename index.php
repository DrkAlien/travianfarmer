<?php
/**
* Version: 1.0.0 ( 1 sept 2019 )
* twitter: @HalmageanD
* Halmagean Daniel
* web: w3bdeveloper.com
*/
require_once 'inc/config.php';
require_once 'inc/farmer.class.php';
$farms = array();
// add farm
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addfarm']) && !empty($_POST['cityid'])) {
    $city = array('id' => $_POST['cityid'],
                  'x'  => '0',
                  'y'  => '0',
                  'last_attack' => ''
    );
    $json = file_get_contents('farms.txt');
    $farms = json_decode($json,true);
    $farms[] = $city;
    // write to file
    $file = fopen("farms.txt", "w") or die("Unable to open file!");
    $txt = json_encode($farms);
    fwrite($file, $txt);
    fclose($file);
    header("Location: http://" . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);exit;
}

// delete farm
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deletefarm']) && !empty($_POST['city_ids'])) {
    $json = file_get_contents('farms.txt');
    $farms = json_decode($json,true);
    foreach($farms as $k=>$v) {
        if(in_array($v['id'],$_POST['city_ids'])) {
            unset($farms[$k]);
        }
    }
    $file = fopen("farms.txt", "w") or die("Unable to open file!");
    $txt = json_encode($farms);
    fwrite($file, $txt);
    fclose($file);
    header("Location: http://" . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);exit;
}

// login cookie creation
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $farm = new travianFarmer();
    $login = $farm->login();
}

// attack villages
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['attack']) && isset($_POST['city_ids'])) {
    # main code here
    $farm = new travianFarmer();
    foreach($_POST['city_ids'] as $k=>$v) {
        // step 1 - set troups for attac on city id ($v)
        $html = $farm->setTroups( $v );
        // step 2 - send attac
        $farm->confirmAttac( $html );
        sleep(rand(3,4));
    }
}
// get farm cities
if(file_exists('farms.txt')) {
    $json = file_get_contents('farms.txt');
    $farms = json_decode($json,true);
} else {
    $file = fopen("farms.txt", "a+") or die("Unable to open file!");
    $txt = json_encode(array());
    fwrite($file, $txt);
    fclose($file);
}

?>
<html>
    <link rel="stylesheet" type="text/css" href="assets/style.css">
<body>
    <div class="header">
        <a href="index.php">Home</a><br/>
    </div>
    <b>Server&nbsp;: <?php echo TRAVIAN_SERVER ?></b><br/>
    <b>User&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo TRAVIAN_USER ?></b><br/>
    <?php if(TRAVIAN_USER == '[Your_Username]') { ?>
        <div class="error">
            Please edit <b>inc/config.php</b> with your travian credentials.
        </div>
    <?php } ?>
    <?php if(isset($login) && $login === true) { ?>
        <div class="success">
            Login successful.
        </div>
    <?php } else if(isset($login) && $login === false) { ?>
            <div class="error">
                Unable to login using <b>inc/config.php</b> credentials.
            </div>
    <?php } ?>
    <table class="farms align-left">
        <tr>
            <td>
                <form action="" method="post">
                    <input type="text" name="cityid" placeholder="village id"/>
                    <input type="submit" name="addfarm" value="Add Farm" />
                </form>
            </td>
            <td></td><td></td>
            <td class="align-right">
                <form action="" method="post">
                    <input type="submit" name="login" value="Login"/>
                </form>
            </td>
        </tr>
    </table>
    <form action="" method="post">
        <table class="farms">
            <tr class="header"><td>Village Id</td><td>Coordinates</td><td>Distance</td><td>Last Attack</td></tr>
            <?php foreach($farms as $k=>$v) { ?>
            <tr>
                <td class="align-left"><input type="checkbox" id="city_<?php echo $v['id'] ?>" name="city_ids[]" value="<?php echo $v['id'] ?>" /> <label class="citytd" for="city_<?php echo $v['id'] ?>"><?php echo $v['id'] ?></label></td>
                <td><?php echo 'x:'.$v['x'].' / y:'.$v['y'] ?></td>
                <td>0</td>
                <td><?php echo $v['last_attack'] ?></td>
            </tr>
            <?php } ?>
            <tr><td colspan="4"><hr/></td></tr>
            <tr><td class="align-left"><input type="submit" name="deletefarm" value="Delete farms"/></td><td></td><td></td><td class="align-right"><input type="submit" name="attack" value="Attack!"/></td></tr>
        </table>
    </form>
</body>
</html>

