<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "post".
 *
 * @property int $id
 * @property int $user_id
 * @property int $type_id
 * @property int|null $main_image_id
 * @property int|null $category_id
 * @property string $title
 * @property string $description
 * @property int $likes_count
 * @property int $visible_id
 * @property int $status_id
 * @property string $created_at
 *
 * @property BoardPost[] $boardPosts
 * @property Board[] $boards
 * @property Category $category
 * @property Favorite[] $favorites
 * @property GenerationItem[] $generationItems
 * @property Generation[] $generations
 * @property ItemLink[] $itemLinks
 * @property Post[] $items
 * @property Like[] $likes
 * @property Image $mainImage
 * @property OutfitItem[] $outfitItems
 * @property OutfitItem[] $outfitItems0
 * @property Post[] $outfits
 * @property PostImage[] $postImages
 * @property PostTag[] $postTags
 * @property ReasonDelete $reasonDelete
 * @property Status $status
 * @property Tag[] $tags
 * @property Type $type
 * @property User $user
 * @property User[] $users
 * @property User[] $users0
 * @property User[] $users1
 * @property User[] $users2
 * @property ViewedPost[] $viewedPosts
 * @property Visible $visible
 * @property Wardrobe[] $wardrobes
 */
class Post extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'post';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['main_image_id', 'category_id'], 'default', 'value' => null],
            [['likes_count'], 'default', 'value' => 0],
            [['user_id', 'type_id', 'title', 'description', 'visible_id', 'status_id'], 'required'],
            [['user_id', 'type_id', 'main_image_id', 'category_id', 'likes_count', 'visible_id', 'status_id'], 'integer'],
            [['description'], 'string'],
            [['created_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => Type::class, 'targetAttribute' => ['type_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['status_id' => 'id']],
            [['visible_id'], 'exist', 'skipOnError' => true, 'targetClass' => Visible::class, 'targetAttribute' => ['visible_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['main_image_id'], 'exist', 'skipOnError' => true, 'targetClass' => Image::class, 'targetAttribute' => ['main_image_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'type_id' => 'Type ID',
            'main_image_id' => 'Main Image ID',
            'category_id' => 'Category ID',
            'title' => 'Title',
            'description' => 'Description',
            'likes_count' => 'Likes Count',
            'visible_id' => 'Visible ID',
            'status_id' => 'Status ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[BoardPosts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBoardPosts()
    {
        return $this->hasMany(BoardPost::class, ['post_id' => 'id']);
    }

    /**
     * Gets query for [[Boards]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBoards()
    {
        return $this->hasMany(Board::class, ['id' => 'board_id'])->viaTable('board_post', ['post_id' => 'id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[Favorites]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFavorites()
    {
        return $this->hasMany(Favorite::class, ['post_id' => 'id']);
    }

    /**
     * Gets query for [[GenerationItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGenerationItems()
    {
        return $this->hasMany(GenerationItem::class, ['post_id' => 'id']);
    }

    /**
     * Gets query for [[Generations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGenerations()
    {
        return $this->hasMany(Generation::class, ['id' => 'generation_id'])->viaTable('generation_item', ['post_id' => 'id']);
    }

    /**
     * Gets query for [[ItemLinks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItemLinks()
    {
        return $this->hasMany(ItemLink::class, ['post_id' => 'id']);
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Post::class, ['id' => 'item_id'])->viaTable('outfit_item', ['outfit_id' => 'id']);
    }

    /**
     * Gets query for [[Likes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLikes()
    {
        return $this->hasMany(Like::class, ['post_id' => 'id']);
    }

    /**
     * Gets query for [[MainImage]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMainImage()
    {
        return $this->hasOne(Image::class, ['id' => 'main_image_id']);
    }

    /**
     * Gets query for [[OutfitItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOutfitItems()
    {
        return $this->hasMany(OutfitItem::class, ['item_id' => 'id']);
    }

    /**
     * Gets query for [[OutfitItems0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOutfitItems0()
    {
        return $this->hasMany(OutfitItem::class, ['outfit_id' => 'id']);
    }

    /**
     * Gets query for [[Outfits]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOutfits()
    {
        return $this->hasMany(Post::class, ['id' => 'outfit_id'])->viaTable('outfit_item', ['item_id' => 'id']);
    }

    /**
     * Gets query for [[PostImages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPostImages()
    {
        return $this->hasMany(PostImage::class, ['post_id' => 'id']);
    }

    /**
     * Gets query for [[PostTags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPostTags()
    {
        return $this->hasMany(PostTag::class, ['post_id' => 'id']);
    }

    /**
     * Gets query for [[ReasonDelete]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReasonDelete()
    {
        return $this->hasOne(ReasonDelete::class, ['post_id' => 'id']);
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id' => 'status_id']);
    }

    /**
     * Gets query for [[Tags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])->viaTable('post_tag', ['post_id' => 'id']);
    }

    /**
     * Gets query for [[Type]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(Type::class, ['id' => 'type_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->viaTable('favorite', ['post_id' => 'id']);
    }

    /**
     * Gets query for [[Users0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers0()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->viaTable('like', ['post_id' => 'id']);
    }

    /**
     * Gets query for [[Users1]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers1()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->viaTable('viewed_post', ['post_id' => 'id']);
    }

    /**
     * Gets query for [[Users2]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers2()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->viaTable('wardrobe', ['post_id' => 'id']);
    }

    /**
     * Gets query for [[ViewedPosts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getViewedPosts()
    {
        return $this->hasMany(ViewedPost::class, ['post_id' => 'id']);
    }

    /**
     * Gets query for [[Visible]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVisible()
    {
        return $this->hasOne(Visible::class, ['id' => 'visible_id']);
    }

    /**
     * Gets query for [[Wardrobes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWardrobes()
    {
        return $this->hasMany(Wardrobe::class, ['post_id' => 'id']);
    }

    public static function find()
    {
        return new PostQuery(get_called_class());
    }
}
