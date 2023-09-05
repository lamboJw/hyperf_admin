<?php

namespace App\Model;

class WechatUser extends Model
{

    protected ?string $table = "wechat_user";

    protected array $fillable = ['uid', 'wc_username', 'wc_uid', 'status'];
}
