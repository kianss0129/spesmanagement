<?php
try{
    $pdo=new PDO('mysql:host=127.0.0.1;dbname=clinic_system','root','');
    $tables=array('job_listings','applications','reports','work_outputs','attendances','interviews');
    $out=array();
    foreach($tables as $t){
        try{
            $res=$pdo->query('SELECT COUNT(*) AS c FROM `'. $t .'`');
            if($res){ $row=$res->fetch(PDO::FETCH_ASSOC); $out[$t]=(int)$row['c']; }
            else { $out[$t]='MISSING'; }
        }catch(PDOException $e){ $out[$t]='MISSING'; }
    }
    echo json_encode($out, JSON_PRETTY_PRINT);
}catch(Exception $e){ echo json_encode(array('error'=>$e->getMessage())); }
