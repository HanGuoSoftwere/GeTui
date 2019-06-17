<?php

namespace HanGuoSoft\GeTui;
/**
 * Created by PhpStorm.
 * User: AltriaPendragon
 * Date: 2019/6/17
 * Time: 9:29
 */
class GeTuiPush
{
    protected $APPKEY = '';
    protected $APPID = '';
    protected $HOST = '';
    protected $MASTERSECRET = '';

    public function __construct()
    {
        $this->APPKEY =config('getui.getui.appKey');
        $this->APPID =config('getui.getui.appId');
        $this->HOST =config('getui.getui.host');
        $this->MASTERSECRET =config('getui.getui.masterSecret');
    }

    /**
     * @param $cid   device_id
     * @param $title       标题
     * @param $content     内容
     * @param $extend    透传
     * @return \Array|string
     */
    public function pushMessageForAndroid($cid, $title, $content, $extend){
        try {
            $igt = new IGeTui($this->HOST,  $this->APPKEY,  $this->MASTERSECRET);
            //消息模版：
            $template = $this->IGtNotyPopLoadTemplate($title, $content, $extend);

            $message = new IGtSingleMessage();
            $message->set_isOffline(true);//是否离线
            $message->set_offlineExpireTime(3600*12*24);//离线时间
            $message->set_data($template);//设置推送消息类型
            //接收方
            $target = new IGtTarget();
            $target->set_appId( $this->APPID);
            $target->set_clientId($cid);

            $rep = $igt->pushMessageToSingle($message, $target);

        }catch (\Exception $exception) {

            return $exception->getMessage();
        }
        return true;
        /**$rep
         *  [result] => ok
        [taskId] => OSS-1125_fd5629a3d19db7961b466de21b1df8ef
        [status] => successed_offline
         */
    }

    /**
     * 苹果单推
     * @param $token
     * @param $title
     * @param $content
     * @return array|mixed|null
     */
    public function pushMessageForIOS($token, $title, $content, $extend)
    {
        $igt = new IGeTui($this->HOST, $this->APPKEY,$this-> MASTERSECRET);
        $template = $this->IGtTransmissionTemplate($title, $content, $extend);
        $message = new IGtSingleMessage();
        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600*12*1000);//离线时间
        $message->set_data($template);//设置推送消息类型
        $message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        try {
            $rep = $igt->pushAPNMessageToSingle($this->APPID, $token, $message);
        }catch(\RequestException $e){
            $requstId =$e->getRequestId();
            //失败时重发
            $rep = $igt->pushAPNMessageToSingle($message, $token, $requstId);
        }
        return $rep;
    }
    /**
     * 通知模板
     * @param $title
     * @param $content
     * @return \IGtNotificationTemplate
     */
    function IGtNotyPopLoadTemplate($title, $content, $extend){
        $template =  new IGtNotificationTemplate();
        $template->set_appId($this->APPID);                      //应用appid
        $template->set_appkey($this->APPKEY);                    //应用appkey
        $template->set_transmissionType(1);               //透传消息类型
        $template->set_transmissionContent($extend);   //透传内容
        $template->set_title($title);                     //通知栏标题
        $template->set_text($content);        //通知栏内容

        $template->set_isRing(true);                      //是否响铃
        $template->set_isVibrate(true);                   //是否震动
        $template->set_isClearable(true);                 //通知栏是否可清除
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $template;
    }




    /**
     * Android与IOS透传模板
     * @param $title
     * @param $content
     * @param array $extend
     * @return \IGtTransmissionTemplate
     * @throws \Exception
     */
    function IGtTransmissionTemplate($title,$content, $extend = []){
        $template =  new IGtTransmissionTemplate();
        $template->set_appId(APPID);//应用appid
        $template->set_appkey(APPKEY);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $template->set_transmissionContent($extend);//透传内容

        $notify =  new IGtNotify();
        $notify->set_title($title);                     //通知栏标题
        $notify->set_content($content);
        $notify->set_payload(json_encode($extend));
        $template->set3rdNotifyInfo($notify);

        //       APN高级推送
        $apn = new IGtAPNPayload();
        foreach ($extend as $k => $v){
            $apn->add_customMsg($k, $v);
        }
        $alertmsg = new DictionaryAlertMsg();
        $alertmsg->body = $content;
        $alertmsg->actionLocKey = "ActionLockey";
        $alertmsg->locKey = "LocKey";
        $alertmsg->locArgs = array("locargs");
        $alertmsg->launchImage = "launchimage";
        //        iOS8.2 支持
        $alertmsg->title = $title;
        $alertmsg->titleLocKey = "TitleLocKey";
        $alertmsg->titleLocArgs = array("TitleLocArg");

        $apn->alertMsg = $alertmsg;
        $apn->badge = 1;
        $apn->sound = "";
        $apn->add_customMsg("payload", $content);
        //      $apn->contentAvailable=1;
        $apn->category = "ACTIONABLE";
        $template->set_apnInfo($apn);

        return $template;
    }

}