<?php

namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class Photo extends Model
{
    //プライマリキーの肩
    protected $keyType = 'string';

    // IDの桁数
    const ID_LENGTH = 12;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if(! array_get($this->attributes, 'id')){
            $this->setId();
        }
    }

    /**
     * ランダムなID値をid属性に代入する
     */
    private function setId()
    {
        $this->attributes['id'] = $this->getRandomId();
    }

    /**
     * ランダムなID値を生成する
     * @retrun string
     */
    private function getRandomId()
    {
        $characters = array_merge(
            range(0, 9), range('a', 'z'),
            range('A', 'Z'), ['-','_']
        );

        $length = count($characters);

        $id = "";

        for ($i = 0; $i < self::ID_LENGTH; $i++) {
            $id .= $characters[random_int(0, $length-1)];
        }

        return $id;
    }

    /**
     * リレーションシップ　usersテーブル
     * @return \Îlluminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo('App\User', 'user_id', 'id', 'users');
    }

    /** 
    * アクセサ - url
    * @return string
    */
    public function getUrlAttribute()
    {
        return Storage::cloud()->url($this->attributes['filename']);
    }

    /** JSONに含めるアクセサ */
    protected $appends = [
        'url','likes_count', 'liked_by_user'
    ];

    /** JSONに含めない属性 */
    protected $hidden = [
        'user_id', 'filename',
        self::CREATED_AT, self::UPDATED_AT,
    ];
    /** JSONに含める属性 */
    protected $visible = [
        'id', 'owner', 'url', 'comments', 'likes_count', 'liked_by_user'
    ];

    // pagination 設定
    protected $perPage = 15;

    /**
     * リレーションシップ　- commentsテーブル
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('App\Comment')->orderBy('id', 'desc');
    }

    /**
     * リレーションシップ　- usersテーブル
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likes()
    {
        return $this->belongsToMany('App\User', 'likes')->withTimestamps();
    }

    /**
     * アクセサ　-kikes_count
     * @return int
     */
    public function getLikesCountAttribute()
    {
        return $this->likes->count();
    }

    /**
     * アクセサ　- liked_by_user
     * @return bookean
     */
    public function getLikedByUserAttribute()
    {
        if (Auth::guest()) {
            return false;
        }

        return $this->likes->contains(function ($user) {
            return $user->id === Auth::user()->id;
        });
    }
}
