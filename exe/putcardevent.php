<?php
    require('../s/common/core.php');
    require(DIR_ROOT.'s/common/exefunction.php');
    $ob_word_list=array(array('<','＜'),array('>','＞'),array('&','＆'));
    // 送信者
    $principal='';
    if(!empty($_POST['principal'])){
        $principal=$_POST['principal'];
    }else{
        exit;
    }
    $nick_name='';
    if(!empty($_POST['nick_name'])){
        $nick_name=$_POST['nick_name'];
    }else{
        $nick_name=$principal;
    }
    // ターゲットの取得
    $cardset_no='';
    $card_id='';
    $element_id='';
    if(isset($_POST['target'])){
        $element_id=$_POST['target'];
        $cardset_no=substr($element_id,7,1);
        if(is_numeric($cardset_no)){
            $cardset_no=(int)$cardset_no;
        }else{
            exit;
        }
        $card_id=substr($element_id,9);
        if(empty($card_id)){
            exit;
        }
    }else{
        exit;
    }
    // イベント別のパラメータ取得
    $param_value='';
    if(isset($_POST['param'])){
        $param_value=$_POST['param'];
    }
    // イベントの取得
    $comment='';
    $event_name='';
    if(isset($_POST['event'])){
        $event_name=$_POST['event'];
    }
    // ファイルロード
    $room_id='';
    $room_dir='';
    $room_file='';
    $room_mirror_file='';
    if(!empty($_POST['xml'])){
        $room_id=basename($_POST['xml']);
        $room_dir=DIR_ROOT.'r/n/'.$room_id.'/';
        $room_file=$room_dir.'data.xml';
        $room_mirror_file=$room_dir.'data-mirror.xml';
        if(!file_exists($room_file)){
            exit;
        }
    }
    $exfilelock=new classFileLock($room_dir,$room_id.'_lockfile',5);
    if($exfilelock->flock($room_dir)){
        if(!empty($event_name)){
            // ルームファイルの読み込み
            if(($room_xml=autoloadXmlFile($room_file,$room_mirror_file))===false){
                $exfilelock->unflock($room_dir);
                exit;
            }
            // changeCardLocation(カードセットNO,ルームXML,カードID,ロケーション,公開フラグ,順位フラグ) 0=一番上に対象のカードを移動 1=一番下に対象のカードを移動
            if($event_name=='cdrm_draw_1t'){ //カードを引く
                if(changeCardLocation($base_card,$cardset_no,$room_xml,$card_id,$principal,$principal,1)){
                    $comment=strLocationName($principal,$room_xml).'さんが';
                    if(($base_card['l']=='_0')||($base_card['l']=='_1')||($base_card['l']=='_2')){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')から';
                    }else{
                        if($base_card['l']==$principal){
                            $comment.='自分の手札('.$cardset_no.')から';
                        }else{
                            $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')から';
                        }
                    }
                    if($base_card['v']=='_1'){
                        $comment.='「'.$base_card['nm'].'」';
                    }else{
                        $comment.='カード';
                    }
                    $comment.='を引きました。';
                }
            }else if($event_name=='cdrm_draw_c1'){ //選んで引く
                if(!empty($param_value)){
                    if(checkCardLocationAndCange($base_card,$cardset_no,$room_xml,$card_id,$param_value,$principal,$principal,1)){
                        $comment=strLocationName($principal,$room_xml).'さんが';
                        if(($base_card['l']=='_0')||($base_card['l']=='_1')||($base_card['l']=='_2')){
                            $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')から';
                        }else{
                            if($base_card['l']==$principal){
                                $comment.='自分の手札('.$cardset_no.')から';
                            }else{
                                $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')から';
                            }
                        }
                    }
                    if($base_card['v']=='_1'){
                        $comment.='「'.$base_card['nm'].'」';
                    }else{
                        $comment.='カード';
                    }
                    $comment.='を選んで引きました。';
                }
            }else if($event_name=='cdrm_put_1t'){ //カードを場に出す
                if(changeCardLocation($base_card,$cardset_no,$room_xml,$card_id,'_2','_1',1)){
                    $comment=strLocationName($principal,$room_xml).'さんが';
                    if(($base_card['l']=='_0')||($base_card['l']=='_1')||($base_card['l']=='_2')){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')から「'.$base_card['nm'].'」を場('.$cardset_no.')に出しました。';
                    }else{
                        if($base_card['l']==$principal){
                            $comment.='自分の手札('.$cardset_no.')から';
                        }else{
                            $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')から';
                        }
                        $comment.='「'.$base_card['nm'].'」を場('.$cardset_no.')に出しました。';
                    }
                }
            }else if($event_name=='cdrm_put_c1'){ //選んで場に出す
                if(!empty($param_value)){
                    if(checkCardLocationAndCange($base_card,$cardset_no,$room_xml,$card_id,$param_value,'_2','_1',1)){
                        $comment=strLocationName($principal,$room_xml).'さんが';
                        if(($base_card['l']=='_0')||($base_card['l']=='_1')||($base_card['l']=='_2')){
                            $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')から「'.$base_card['nm'].'」を選んで場('.$cardset_no.')に出しました。';
                        }else{
                            if($base_card['l']==$principal){
                                $comment.='自分の手札('.$cardset_no.')から';
                            }else{
                                $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')から';
                            }
                            $comment.='「'.$base_card['nm'].'」を選んで場('.$cardset_no.')に出しました。';
                        }
                    }
                }
            }else if($event_name=='cdrm_drop_1t'){ //カードを捨て札に置く
                if(changeCardLocation($base_card,$cardset_no,$room_xml,$card_id,'_1','_1',0)){
                    $comment=strLocationName($principal,$room_xml).'さんが';
                    if(($base_card['l']=='_0')||($base_card['l']=='_1')||($base_card['l']=='_2')){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')から「'.$base_card['nm'].'」を捨てました。';
                    }else{
                        if($base_card['l']==$principal){
                            $comment.='自分の手札('.$cardset_no.')から';
                        }else{
                            $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')から';
                        }
                        $comment.='「'.$base_card['nm'].'」を捨てました。';
                    }
                }
            }else if($event_name=='cdrm_drop_ab'){ //全てのカードを捨て札に置く
                if(changeAllCardLocation($base_card,$cardset_no,$room_xml,$card_id,'_1','_1',0)){
                    $comment=strLocationName($principal,$room_xml).'さんが';
                    if(($base_card['l']=='_0')||($base_card['l']=='_1')||($base_card['l']=='_2')){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')から全てのカードを捨て札('.$cardset_no.')に置きました。';
                    }else{
                        if($base_card['l']==$principal){
                            $comment.='全ての手札('.$cardset_no.')';
                        }else{
                            $comment.=strLocationName($base_card['l'],$room_xml).'さんの全ての手札('.$cardset_no.')';
                        }
                        $comment.='を捨てました。';
                    }
                }
            }else if($event_name=='cdrm_drop_c1'){ //選んで捨て札に置く
                if(!empty($param_value)){
                    if(checkCardLocationAndCange($base_card,$cardset_no,$room_xml,$card_id,$param_value,'_1','_1',0)){
                        $comment=strLocationName($principal,$room_xml).'さんが';
                        if(($base_card['l']=='_0')||($base_card['l']=='_1')||($base_card['l']=='_2')){
                            $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')から「'.$base_card['nm'].'」を選んで捨てました。';
                        }else{
                            if($base_card['l']==$principal){
                                $comment.='自分の手札('.$cardset_no.')から';
                            }else{
                                $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')から';
                            }
                            $comment.='「'.$base_card['nm'].'」を選んで捨てました。';
                        }
                    }
                }
            }else if($event_name=='cdrm_donate_1t'){ //カードを渡す
                if(!empty($param_value)){
                    if(changeCardLocation($base_card,$cardset_no,$room_xml,$card_id,$param_value,$param_value,1)){
                        $comment=strLocationName($principal,$room_xml).'さんが';
                        if(($base_card['l']=='_0')||($base_card['l']=='_1')||($base_card['l']=='_2')){
                            $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')から';
                        }else{
                            if($base_card['l']==$principal){
                                $comment.='自分の手札('.$cardset_no.')の';
                            }else{
                                $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')から';
                            }
                        }
                        if($base_card['v']=='_1'){
                            $comment.='「'.$base_card['nm'].'」を';
                        }else{
                            $comment.='カードを';
                        }
                        if(($param_value=='_0')||($param_value=='_1')||($param_value=='_2')){
                            $comment.=strLocationName($param_value,$room_xml).'('.$cardset_no.')に置きました。';
                        }else{
                            $comment.=strLocationName($param_value,$room_xml).'さんに渡しました。';
                        }
                    }
                }
            }else if($event_name=='cdrm_back_1b'){ //カードを山札に戻す
                if(changeCardLocation($base_card,$cardset_no,$room_xml,$card_id,'_0','_0',1)){
                    $comment=strLocationName($principal,$room_xml).'さんが';
                    if(($base_card['l']=='_0')||($base_card['l']=='_1')||($base_card['l']=='_2')){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')から';
                    }else{
                        if($base_card['l']==$principal){
                            $comment.='自分の手札('.$cardset_no.')の';
                        }else{
                            $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')から';
                        }
                    }
                    if($base_card['v']=='_1'){
                        $comment.='「'.$base_card['nm'].'」';
                    }else{
                        $comment.='カード';
                    }
                    $comment.='を山札('.$cardset_no.')に戻しました。';
                }
            }else if($event_name=='cdrm_back_ab'){ //全てカードを山札に戻す
                if(changeAllCardLocation($base_card,$cardset_no,$room_xml,$card_id,'_0','_0',1)){
                    $comment=strLocationName($principal,$room_xml).'さんが';
                    if(($base_card['l']=='_0')||($base_card['l']=='_1')||($base_card['l']=='_2')){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')から全てのカードを山札('.$cardset_no.')に戻しました。';
                    }else{
                        if($base_card['l']==$principal){
                            $comment.='自分の全ての手札('.$cardset_no.')を';
                        }else{
                            $comment.=strLocationName($base_card['l'],$room_xml).'さんの全ての手札('.$cardset_no.')を';
                        }
                        $comment.='山札('.$cardset_no.')に戻しました。';
                    }
                }
            }else if($event_name=='cdrm_back_c1'){ //選んで山札に戻す
                if(!empty($param_value)){
                    if(checkCardLocationAndCange($base_card,$cardset_no,$room_xml,$card_id,$param_value,'_0','_0',1)){
                        $comment=strLocationName($principal,$room_xml).'さんが';
                        if(($base_card['l']=='_0')||($base_card['l']=='_1')||($base_card['l']=='_2')){
                            $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')から';
                        }else{
                            if($base_card['l']==$principal){
                                $comment.='自分の手札('.$cardset_no.')の';
                            }else{
                                $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')から';
                            }
                        }
                        if($base_card['v']=='_1'){
                            $comment.='「'.$base_card['nm'].'」';
                        }else{
                            $comment.='カード';
                        }
                        $comment.='を山札('.$cardset_no.')に戻しました。';
                    }
                }
            }else if($event_name=='cdrm_shuffle'){ //シャッフルする
                if($card_id=='card_stock'){
                    if(shuffleCard($cardset_no,$room_xml,'_0',1,0)){
                        $comment=strLocationName($principal,$room_xml).'さんが山札('.$cardset_no.')をシャッフルしました。';
                    }
                }elseif($card_id=='discard_stock'){
                    if(shuffleCard($cardset_no,$room_xml,'_1',0,1)){
                        $comment=strLocationName($principal,$room_xml).'さんが捨て札('.$cardset_no.')をシャッフルしました。';
                    }
                }
            }else if($event_name=='cdrm_public'){ //全員に見せる（公開）
                if(changeCardVisible($base_card,$cardset_no,$room_xml,$card_id,'v','_1')){
                    $comment=strLocationName($principal,$room_xml).'さんが';
                    if(($base_card['l']=='_0')||($base_card['l']=='_1')){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')の一番上の「'.$base_card['nm'].'」を公開しました。';
                    }elseif($base_card['l']=='_2'){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')の「'.$base_card['nm'].'」を公開しました。';
                    }else{
                        if($base_card['l']==$principal){
                            $comment.='自分の手札('.$cardset_no.')の';
                        }else{
                            $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')の';
                        }
                        $comment.='「'.$base_card['nm'].'」を公開しました。';
                    }
                }
            }else if($event_name=='cdrm_private'){ //自分だけ見る（非公開）
                if(changeCardVisible($base_card,$cardset_no,$room_xml,$card_id,'v',$principal)){
                    $comment=strLocationName($principal,$room_xml).'さんが';
                    if(($base_card['l']=='_0')||($base_card['l']=='_1')){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')の一番上の';
                    }elseif($base_card['l']=='_2'){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')の';
                    }else{
                        if($base_card['l']==$principal){
                            $comment.='自分の手札('.$cardset_no.')の';
                        }else{
                            $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')の';
                        }
                    }
                    if($base_card['v']=='_1'){
                        $comment.='「'.$base_card['nm'].'」';
                    }else{
                        $comment.='カード';
                    }
                    $comment.='を自分だけ見えるようにしました。';
                }
            }else if($event_name=='cdrm_hide'){ //カードを伏せる（非公開）
                if(changeCardVisible($base_card,$cardset_no,$room_xml,$card_id,'v','_0')){
                    $comment=strLocationName($principal,$room_xml).'さんが';
                    if(($base_card['l']=='_0')||($base_card['l']=='_1')){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')の一番上の';
                    }elseif($base_card['l']=='_2'){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')の';
                    }else{
                        if($base_card['l']==$principal){
                            $comment.='自分の手札('.$cardset_no.')の';
                        }else{
                            $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')の';
                        }
                    }
                    if($base_card['v']=='_1'){
                        $comment.='「'.$base_card['nm'].'」';
                    }else{
                        $comment.='カード';
                    }
                    $comment.='を伏せました。';
                }
            }else if($event_name=='cdrm_front'){ //正面に向ける
                if(changeCardVisible($base_card,$cardset_no,$room_xml,$card_id,'d',0)){
                    $comment=strLocationName($principal,$room_xml).'さんが';
                    if(($base_card['l']=='_0')||($base_card['l']=='_1')){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')の一番上の';
                    }elseif($base_card['l']=='_2'){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')の';
                    }else{
                        if($base_card['l']==$principal){
                            $comment.='自分の手札('.$cardset_no.')の';
                        }else{
                            $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')の';
                        }
                    }
                    if($base_card['v']=='_1'){
                        $comment.='「'.$base_card['nm'].'」';
                    }else{
                        $comment.='カード';
                    }
                    $comment.='を正面に向けました。';
                }
            }else if($event_name=='cdrm_right'){ //右向きにする
                if(changeCardVisible($base_card,$cardset_no,$room_xml,$card_id,'d',90)){
                    $comment=strLocationName($principal,$room_xml).'さんが';
                    if(($base_card['l']=='_0')||($base_card['l']=='_1')){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')の一番上の';
                    }elseif($base_card['l']=='_2'){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')の';
                    }else{
                        if($base_card['l']==$principal){
                            $comment.='自分の手札('.$cardset_no.')の';
                        }else{
                            $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')の';
                        }
                    }
                    if($base_card['v']=='_1'){
                        $comment.='「'.$base_card['nm'].'」';
                    }else{
                        $comment.='カード';
                    }
                    $comment.='を右に向けました。';
                }
            }else if($event_name=='cdrm_left'){ //左向きにする
                if(changeCardVisible($base_card,$cardset_no,$room_xml,$card_id,'d',270)){
                    $comment=strLocationName($principal,$room_xml).'さんが';
                    if(($base_card['l']=='_0')||($base_card['l']=='_1')){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')の一番上の';
                    }elseif($base_card['l']=='_2'){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')の';
                    }else{
                        if($base_card['l']==$principal){
                            $comment.='自分の手札('.$cardset_no.')の';
                        }else{
                            $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')の';
                        }
                    }
                    if($base_card['v']=='_1'){
                        $comment.='「'.$base_card['nm'].'」';
                    }else{
                        $comment.='カード';
                    }
                    $comment.='を左に向けました。';
                }
            }else if($event_name=='cdrm_reverse'){ //逆向きにする
                if(changeCardVisible($base_card,$cardset_no,$room_xml,$card_id,'d',180)){
                    $comment=strLocationName($principal,$room_xml).'さんが';
                    if(($base_card['l']=='_0')||($base_card['l']=='_1')){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')の一番上の';
                    }elseif($base_card['l']=='_2'){
                        $comment.=strLocationName($base_card['l'],$room_xml).'('.$cardset_no.')の';
                    }else{
                        if($base_card['l']==$principal){
                            $comment.='自分の手札('.$cardset_no.')の';
                        }else{
                            $comment.=strLocationName($base_card['l'],$room_xml).'さんの手札('.$cardset_no.')の';
                        }
                    }
                    if($base_card['v']=='_1'){
                        $comment.='「'.$base_card['nm'].'」';
                    }else{
                        $comment.='カード';
                    }
                    $comment.='を逆に向けました。';
                }
            }else if($event_name=='reference_card_stock'){ // add. 2016.12.26
                $comment=strLocationName($principal,$room_xml).'さんが';
                $comment.='山札('.$cardset_no.')を参照しています。';
            }else if($event_name=='reference_discard_stock'){ // add. 2016.12.26
                $comment=strLocationName($principal,$room_xml).'さんが';
                $comment.='捨て札('.$cardset_no.')を参照しています。';
            }
            if(!empty($comment)){
                $BContent=$room_xml->body->addChild('content');
                $BContent->addAttribute('id',creatChatMsgId());
                $BContent->addChild('date',time());
                $BContent->addChild('text',htmlentities($comment,ENT_XML1));
                $BContent->addChild('chat_color','#000000');
                $BContent->addChild('ctyp',1);
                $BContent->addChild('author','システム');
            }
            // ルームの保存
            if(!saveRoomXmlFile($room_xml,$room_file,$principal,$nick_name,0)){
                echo 'ERR=情報を更新できませんでした。';
            }
        }
    }else{
        echo 'ERR=アクセスが集中したため送信に失敗しました。';
    }
    $exfilelock->unflock($room_dir);
    exit;