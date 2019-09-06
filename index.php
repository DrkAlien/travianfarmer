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
$error = '';
$success = '';
// add farm
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addfarm']) && !empty($_POST['cityid'])) {
    $city = array('id' => $_POST['cityid'],
                  'x'  => '0',
                  'y'  => '0',
                  'name' => '',
                  'last_attack' => ''
    );
    $json = file_get_contents('farms.txt');
    $farms = json_decode($json,true);
    #echo '<pre/>';print_r($farms);exit;
    foreach($farms as $k=>$v) {
        if($v['id'] == $_POST['cityid']) {
            $error = 'This village is already a farm.';
            break;
        }
    }
    $farms[] = $city;
    if(strlen($error) == 0) {
        // write to file
        $file = fopen("farms.txt", "w") or die("Unable to open file!");
        $txt = json_encode($farms);
        fwrite($file, $txt);
        fclose($file);
        header("Location: http://" . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);exit;
    }
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
    $farm = new travianFarmer($_POST['t_amount'], $_POST['t_type']);
    foreach($_POST['city_ids'] as $k=>$v) {
        // step 1 - set troups for attac on city id ($v)
        $html = $farm->setTroups( $v );
        // step 2 - send attac
        $villageData = $farm->confirmAttac( $html );
        if($villageData === false) {
            $error = 'Please login first or village id: #'.$v.' may not exists anymore.';
            break;
        } else {
            $success = 'Last attacked city was: #'.$v;
        }
        // update village data
        $json = file_get_contents('farms.txt');
        $farms = json_decode($json,true);
        foreach($farms as $k=>$village) {
            if($village['id'] == $v) {
                $farms[$k]['x'] = $villageData['x'];
                $farms[$k]['y'] = $villageData['y'];
                $farms[$k]['name'] = $villageData['name'];
                $farms[$k]['last_attack'] = date('Y-m-d H:i:s');
                break;
            }
        }
        // write to file
        $file = fopen("farms.txt", "w") or die("Unable to open file!");
        $txt = json_encode($farms);
        fwrite($file, $txt);
        fclose($file);

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
    <b>User&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo TRAVIAN_USER ?></b><br/><br/>
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

    <?php if(strlen($error) > 0) { ?>
            <div class="error">
                <?php echo $error ?>
            </div>
    <?php } ?>
    <?php if(strlen($success) > 0) { ?>
            <div class="success">
                <?php echo $success ?>
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
            <tr>
                <td class="align-left" colspan="4">
                    <label>Troups:</label> <input type="text" name="t_amount" placeholder="troups amount" value="3"style="width:40px;"/>
                    <input type="radio" name="t_type" value="t1" id="t1" checked/> <label for="t1" title="Legionaire/Clubswinger/Phalanx">Base Troup</label>
                    <input type="radio" name="t_type" value="t2" id="t2"/> <label for="t2" title="Praetorian/Spearman/Swordsmen">2'nd Troup</label>
                    <input type="radio" name="t_type" value="t3" id="t3"/> <label for="t3" title="Imperian/Axeman/Theutates Thunder">3'rd Troup</label>
                </td>
            </tr>
        </table>

        <table class="farms">
            <tr class="header"><th>Village Id</th><th>Coordinates</th><th class="pointer">Distance &#x21D5;</th><th class="pointer">Last Attack &#x21D5;</th></tr>
            <?php foreach($farms as $k=>$v) {
                    $v['name'] = (isset($v['name']))? $v['name']:'';
            ?>
            <tr>
                <td class="align-left"><?php echo $k+1 ?>. <input type="checkbox" id="city_<?php echo $v['id'] ?>" name="city_ids[]" value="<?php echo $v['id'] ?>" /> <label class="citytd" for="city_<?php echo $v['id'] ?>">#<?php echo $v['id'].': '.$v['name'] ?></label></td>
                <td>
                    <?php if($v['x'] != 0 && $v['y'] != 0) { ?>
                        <a href="<?php echo TRAVIAN_SERVER ?>/position_details.php?x=<?php echo $v['x'] ?>&y=<?php echo $v['y'] ?>" target="_blank"><?php echo 'x:'.$v['x'].' / y:'.$v['y'] ?></a>
                    <?php } ?>
                </td>
                <td>
                    <?php if($v['x'] != 0 && $v['y'] != 0) {
                        echo '<b>'.travianFarmer::calculateDistance($v['x'],$v['y']).'<b>';
                    } ?>
                </td>
                <td><?php echo date('d M H:i:s',strtotime($v['last_attack'])) ?></td>
            </tr>
            <?php } ?>

        </table>

        <table class="farms">
            <tr><td colspan="4"><hr/></td></tr>
            <tr><td class="align-left"><input type="submit" name="deletefarm" value="Delete farms"/></td><td></td><td></td><td class="align-right"><input type="submit" name="attack" value="Attack!"/></td></tr>
        </table>
    </form>

    <script>
        const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;
        const comparer = (idx, asc) => (a, b) => ((v1, v2) =>
            v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
            )(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

        // do the work...
        document.querySelectorAll('th').forEach(th => th.addEventListener('click', (() => {
            const table = th.closest('table');
            Array.from(table.querySelectorAll('tr:nth-child(n+2)'))
                .sort(comparer(Array.from(th.parentNode.children).indexOf(th), this.asc = !this.asc))
                .forEach(tr => table.appendChild(tr) );
        })));
    </script>
</body>
</html>

