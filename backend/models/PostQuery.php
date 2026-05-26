<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\Expression;

class PostQuery extends ActiveQuery
{

    public function active()
    {
        return $this->andWhere(['post.status_id' => 1, 'post.visible_id' => 1]);
    }

    // Публичные посты + посты автора на модерации
    public function activeOrOwnerModeration(?int $userId): self
    {
        if (!$userId) {
            return $this->active();
        }

        return $this->andWhere([
            'or',
            ['post.status_id' => 1, 'post.visible_id' => 1],
            ['post.status_id' => 4, 'post.user_id' => $userId],
        ]);
    }

    public function public()
    {
        return $this->andWhere(['post.visible_id' => 1]);
    }

    public function sortByRelevance()
    {
        $sql = "(post.likes_count + 1) / POW(TIMESTAMPDIFF(HOUR, post.created_at, NOW()) + 2, 1.8)";
        return $this->addOrderBy([new \yii\db\Expression($sql . " DESC")]);
    }

    public function exceptIds($ids)
    {
        if (empty($ids)) return $this;
        return $this->andWhere(['not in', 'post.id', array_unique($ids)]);
    }

    public function byTags($tagIds)
    {
        if (empty($tagIds)) return $this->andWhere('0=1');

        return $this->joinWith('postTags', false)
            ->andWhere(['post_tag.tag_id' => $tagIds])
            ->groupBy('post.id')
            ->orderBy([new Expression('COUNT(post_tag.tag_id) DESC')]);
    }
}
