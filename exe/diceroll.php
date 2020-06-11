<?php
    require('../s/common/core.php');
    require(DIR_ROOT.'s/common/exefunction.php');
    $result=false;
    $count_number=1;
    if(!empty($_POST['dcn'])){
        $count_number=$_POST['dcn'];
    }
    $dice_surface=6;
    if(!empty($_POST['ds'])){
        $dice_surface=$_POST['ds'];
    }
    $chat_color='#000000';
    if(!empty($_POST['chat_color'])){
        $chat_color=$_POST['chat_color'];
    }
    $call_name='ななし';
    if(!empty($_POST['call_name'])){
        $call_name=$_POST['call_name'];
    }
    $chat_type=1;
    if(!empty($_POST['chat_type'])){
        $chat_type=$_POST['chat_type'];
    }
    $principal='';
    if(!empty($_POST['principal'])){
        $principal=$_POST['principal'];
    }
    $nick_name='';
    if(!empty($_POST['nick_name'])){
        $nick_name=$_POST['nick_name'];
    }else{
        $nick_name=$principal;
    }
    $room_id='';
    $room_dir='';
    $room_file='';
    $room_mirror_file='';
    if(!empty($_POST['xml'])){
        $room_id=basename($_POST['xml']);
        $room_dir=DIR_ROOT.'r/n/'.$room_id.'/';
        $room_file=$room_dir.'data.xml';
        $room_mirror_file=$room_dir.'data-mirror.xml';
        if(file_exists($room_file)){
            $result=true;
        }
    }
    $exfilelock=new classFileLock($room_dir,$room_id.'_lockfile',5);
    if($exfilelock->flock($room_dir)){
        if($result==true){
            // ルームファイルの読み込み
            if(($room_xml=autoloadXmlFile($room_file,$room_mirror_file))===false){
                $exfilelock->unflock($room_dir);
                exit;
            }
            $comment='';
            $say_man='システム';
            $BContent=$room_xml->body->addChild('content');
            $say_time=time();
			$comment_id=creatChatMsgId($principal,$say_time);
            $BContent->addAttribute('id',$comment_id);
            if($say_time>(int)$room_xml->head->dice_roll->dr_count){
                $str_surface='';
                $str_daice='';
                $total_roll=0;
                $dice=array();
                $total_roll=rollDice($count_number,$dice_surface,$dice,DICE_ROLL_LIMIT,DICE_SURFACE_LIMIT);
                $comment=$call_name.'さんのロール('.$count_number.'D'.$dice_surface.') → '.$total_roll.' (';
                $i=0;
                foreach($dice as $value){
                    if($i!=0){
                        $str_surface.=',';
                        $str_daice.=',';
                    }
                    $str_surface.=$value[0];
                    $str_daice.=$value[1];
                    $i++;
                }
                $comment.=$str_daice.')';
                $room_xml->head->dice_roll->dr_count=$say_time+3;
                $room_xml->head->dice_roll->dr_surface=$dice_surface;
                $room_xml->head->dice->d_surface=$str_surface;
                $room_xml->head->dice->d_number=$str_daice;
                $room_xml->head->dice->d_result=$total_roll;
                $BContent->addChild('date',$say_time+3);
                $BContent->addChild('text',htmlentities($comment,ENT_XML1));
                $BContent->addChild('chat_color',$chat_color);
                $BContent->addChild('ctyp',$chat_type);
                $BContent->addChild('author',htmlentities($say_man,ENT_XML1));
                // ルームの保存
                if(saveRoomXmlFile($room_xml,$room_file,$principal,$nick_name,0)){
                    // phpログ保存
                    @include(DIR_ROOT.'s/common/exewritelog.php');
                }else{
                    echo 'ERR=情報を更新できませんでした。';
                }
            }else{
                echo 'ERR=現在ロール中のため、'.$call_name.'さんのロールは行われませんでした。';
            }
        }
    }else{
        echo 'ERR=アクセスが集中したため送信に失敗しました。';
    }
    $exfilelock->unflock($room_dir);
    exit;