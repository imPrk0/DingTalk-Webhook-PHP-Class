<?php
/**
 * 钉钉群机器人 Webhook 方法类
 * @author Prk
 */

class DingTalkWebHook {

    private string $token;
    private string $sign;

    /**
     * 构造器
     * @author
     * 
     * @param string $token 机器人Token
     * @param string $sign 签名
     */
    function __construct(string $token, string $sign) {
        $this->token = $token;
        $this->sign = $sign;
    }

    /**
     * Text 文本类型 (群聊、单聊）
     * @author Prk
     * 
     * @param string $text 要发送的文本内容
     * @param array $mobiles （可选）被@人的手机号
     * @param array $userIds （可选）被@人的用户 ID
     * @param boolean $atAll （可选）是否@所有人
     * 
     * @return array 接口返回的内容
     */
    public function sendText(string $text, array $mobiles = [], array $userIds = [], bool $atAll = false): array {
        return $this->dingTalkWebhook([
            'msgtype'   =>  'text',
            'text'      =>  [
                'content'   =>  $text,
            ],
            'at'        =>  [
                'atMobiles' =>  $mobiles,
                'atUserIds' =>  $userIds,
                'isAtAll'   =>  $atAll
            ]
        ]);
    }

    /**
     * Markdown 文本类型 （单聊、群聊）
     * @author Prk
     * 
     * @param string $md 要发送的 Markdown 内容
     * @param string $title 首屏会话透出的展示内容
     * @param array $mobiles （可选）被@人的手机号
     * @param array $userIds （可选）被@人的用户 ID
     * @param boolean $atAll （可选）是否@所有人
     * 
     * @return array 接口返回的内容
     */
    public function sendMd(string $md, string $title, array $mobiles = [], array $userIds = [], bool $atAll = false): array {
        return $this->dingTalkWebhook([
            'msgtype'   =>  'markdown',
            'markdown'  =>  [
                'title'     =>  $title,
                'text'      =>  $md
            ],
            'at'        =>  [
                'atMobiles' =>  $mobiles,
                'atUserIds' =>  $userIds,
                'isAtAll'   =>  $atAll
            ]
        ]);
    }

    /**
     * ActionCard 整体跳转消息 （单聊、群聊）
     * @author Prk
     * 
     * @param string $md 要发送的 Markdown 内容
     * $param string $title 首屏会话透出的展示内容
     * @param string $btnTitle 按钮标题文字
     * @param string $btnLink 按钮链接地址
     * 
     * @return array 接口返回的内容
     */
    public function sendAC(string $md, string $title, string $btnTitle, string $btnLink): array {
        return $this->dingTalkWebhook([
            'msgtype'       =>  'actionCard',
            'actionCard'    =>  [
                'title'         =>  $title,
                'text'          =>  $md,
                'singleTitle'   =>  $btnTitle,
                'singleURL'     =>  $btnLink
            ]
        ]);
    }

    /**
     * ActionCard 独立跳转消息 （单聊、群聊）
     * @author Prk
     * 
     * @param string $md 要发送的 Markdown 内容
     * $param string $title 首屏会话透出的展示内容
     * @param boolean $l 是否竖向排列按钮
     * @param array $btns 按钮群（string title 按钮标题，string actionURL 按钮地址）
     * 
     * @return array 接口返回的内容
     */
    public function sendAC2(string $md, string $title, bool $l = true, array $btns): array {
        return $this->dingTalkWebhook([
            'msgtype'       =>  'actionCard',
            'actionCard'    =>  [
                'title'         =>  $title,
                'text'          =>  $md,
                'btnOrientation'=>  ($l ? '0' : '1')
            ]
        ]);
    }

    /**
     * FeedCard 消息 （群聊）
     * @author Prk
     * 
     * @param array $links 链接（string title 单链接标题，string messageURL 单链接地址，string picURL 图片直链地址）
     * 
     * @return array 接口返回的内容
     */
    public function sendFC(array $links): array {
        return $this->dingTalkWebhook([
            'msgtype'   =>  'feedCard',
            'feedCard'  =>  [
                'links'     =>  $links
            ]
        ]);
    }

    /**
     * Link 消息 （单聊、群聊）
     * @author Prk
     * 
     * @param string $text 链接内容（如果太长只会部分展示）
     * @param string $title 链接标题
     * @param $picUrl 图片地址可为空
     * @param string $messageUrl 链接地址
     * 
     * @return array 接口返回的内容
     */
    public function sendLink(string $text, string $title, $picUrl = null, string $messageUrl): array {
        return $this->dingTalkWebhook([
            'msgtype'   =>  'link',
            'link'      =>  [
                'text'      =>  $text,
                'title'     =>  $title,
                'picUrl'    =>  $picUrl,
                'messageUrl'=>  $messageUrl
            ]
        ]);
    }

    /**
     * 发送内容
     * @author Prk
     * 
     * @param array $msg 消息
     * 
     * @return array 接口返回的内容
     */
    private function dingTalkWebhook(array $msg): array {
        $timestamp = explode(' ', microtime());
        $timestamp = (float)sprintf('%.0f', (floatval($timestamp[0]) + floatval($timestamp[1])) * 1000);
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL             =>  'https://oapi.dingtalk.com/robot/send?' . http_build_query([
                'access_token'  =>  $this->token,
                'timestamp'     =>  $timestamp,
                'sign'          =>  $this->getSign($timestamp)
            ]),
            CURLOPT_POST            =>  1,
            CURLOPT_CONNECTTIMEOUT  =>  5,
            CURLOPT_HTTPHEADER      =>  [
                'Content-Type: Application/json; charset=utf-8'
            ],
            CURLOPT_POSTFIELDS      =>  json_encode($msg),
            CURLOPT_RETURNTRANSFER  =>  true,
            CURLOPT_SSL_VERIFYHOST  =>  0,
            CURLOPT_SSL_VERIFYPEER  =>  0
        ]);
        $content = curl_exec($ch);
        curl_close($ch);
        return json_decode($content, true);
    }

    /**
     * 算签名
     * @author Prk
     * 
     * @param $timestamp 时间戳
     * 
     * @return string 签名
     */
    private function getSign($timestamp): string {
        return urlencode(base64_encode(hash_hmac('sha256', $timestamp . "\n" . $this->sign, $this->sign, true)));
    }
}
