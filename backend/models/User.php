<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $login
 * @property string $password
 * @property string $email
 * @property string|null $avatar_path
 * @property string|null $background_path
 * @property int $role_id
 * @property string $auth_key
 * @property int $quantity_fitcoins
 * @property int $privacy_policy_accepted
 * @property int $oferta_accepted
 * @property int $email_verified
 * @property string|null $verification_token
 * @property string|null $verification_token_expires
 *
 * @property Board[] $boards
 * @property Favorite[] $favorites
 * @property Generation[] $generations
 * @property Like[] $likes
 * @property Post[] $posts
 * @property Post[] $posts0
 * @property Post[] $posts1
 * @property Post[] $posts2
 * @property Post[] $posts3
 * @property ReasonDelete[] $reasonDeletes
 * @property UserRole $role
 * @property Transaction[] $transactions
 * @property ViewedPost[] $viewedPosts
 * @property Wardrobe[] $wardrobes
 */
class User extends ActiveRecord implements IdentityInterface
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['avatar_path', 'background_path', 'verification_token', 'verification_token_expires'], 'default', 'value' => null],
            [['role_id'], 'default', 'value' => 1],
            [['quantity_fitcoins'], 'default', 'value' => 200],
            [['email_verified'], 'default', 'value' => 0],
            [['login', 'password', 'email', 'auth_key', 'privacy_policy_accepted', 'oferta_accepted'], 'required'],
            [['role_id', 'quantity_fitcoins', 'privacy_policy_accepted', 'oferta_accepted', 'email_verified'], 'integer'],
            [['verification_token_expires'], 'safe'],
            [['login', 'password', 'email', 'avatar_path', 'background_path', 'auth_key', 'verification_token'], 'string', 'max' => 255],
            [['email'], 'unique'],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserRole::class, 'targetAttribute' => ['role_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'login' => 'Login',
            'password' => 'Password',
            'email' => 'Email',
            'avatar_path' => 'Avatar Path',
            'background_path' => 'Background Path',
            'role_id' => 'Role ID',
            'auth_key' => 'Auth Key',
            'quantity_fitcoins' => 'Quantity Fitcoins',
            'privacy_policy_accepted' => 'Privacy Policy Accepted',
            'oferta_accepted' => 'Oferta Accepted',
            'email_verified' => 'Email Verified',
            'verification_token' => 'Verification Token',
            'verification_token_expires' => 'Verification Token Expires',
        ];
    }

    /**
     * Gets query for [[Boards]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBoards()
    {
        return $this->hasMany(Board::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Favorites]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFavorites()
    {
        return $this->hasMany(Favorite::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Generations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGenerations()
    {
        return $this->hasMany(Generation::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Likes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLikes()
    {
        return $this->hasMany(Like::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Posts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Posts0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts0()
    {
        return $this->hasMany(Post::class, ['id' => 'post_id'])->viaTable('favorite', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Posts1]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts1()
    {
        return $this->hasMany(Post::class, ['id' => 'post_id'])->viaTable('like', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Posts2]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts2()
    {
        return $this->hasMany(Post::class, ['id' => 'post_id'])->viaTable('viewed_post', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Posts3]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPosts3()
    {
        return $this->hasMany(Post::class, ['id' => 'post_id'])->viaTable('wardrobe', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[ReasonDeletes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReasonDeletes()
    {
        return $this->hasMany(ReasonDelete::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Role]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(UserRole::class, ['id' => 'role_id']);
    }

    /**
     * Gets query for [[Transactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(Transaction::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[ViewedPosts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getViewedPosts()
    {
        return $this->hasMany(ViewedPost::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Wardrobes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWardrobes()
    {
        return $this->hasMany(Wardrobe::class, ['user_id' => 'id']);
    }

    public function getViewedPost()
    {
        return $this->hasMany(ViewedPost::class, ['user_id' => 'id']);
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public static function findByemail($email): User | null
    {
        return static::findOne(['email' => $email]);
    }

    public function validatePassword($password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public function getIsAccount()
    {
        return $this->role_id == 1;
    }

    public function getIsAdmin()
    {
        return $this->role_id == 2;
    }
}
