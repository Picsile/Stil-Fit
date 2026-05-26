<?php

use yii\db\Migration;

class m260516_091840_create_foreign_keys extends Migration
{
    public function safeUp()
    {
        $this->addForeignKey(
            'category_ibfk_1',
            '{{%category}}',
            ['parent_id'],
            '{{%category}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'post_image_ibfk_2',
            '{{%post_image}}',
            ['post_id'],
            '{{%post}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'post_tag_ibfk_1',
            '{{%post_tag}}',
            ['post_id'],
            '{{%post}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'viewed_post_ibfk_1',
            '{{%viewed_post}}',
            ['post_id'],
            '{{%post}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'wardrobe_ibfk_1',
            '{{%wardrobe}}',
            ['post_id'],
            '{{%post}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'post_ibfk_5',
            '{{%post}}',
            ['category_id'],
            '{{%category}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'reason_delete_ibfk_1',
            '{{%reason_delete}}',
            ['post_id'],
            '{{%post}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'board_post_ibfk_1',
            '{{%board_post}}',
            ['post_id'],
            '{{%post}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'favorite_ibfk_1',
            '{{%favorite}}',
            ['post_id'],
            '{{%post}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'generation_item_ibfk_2',
            '{{%generation_item}}',
            ['post_id'],
            '{{%post}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'item_link_ibfk_1',
            '{{%item_link}}',
            ['post_id'],
            '{{%post}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'like_ibfk_1',
            '{{%like}}',
            ['post_id'],
            '{{%post}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'outfit_item_ibfk_1',
            '{{%outfit_item}}',
            ['item_id'],
            '{{%post}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'outfit_item_ibfk_2',
            '{{%outfit_item}}',
            ['outfit_id'],
            '{{%post}}',
            ['id'],
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('outfit_item_ibfk_2', '{{%outfit_item}}');
        $this->dropForeignKey('outfit_item_ibfk_1', '{{%outfit_item}}');
        $this->dropForeignKey('like_ibfk_1', '{{%like}}');
        $this->dropForeignKey('item_link_ibfk_1', '{{%item_link}}');
        $this->dropForeignKey('generation_item_ibfk_2', '{{%generation_item}}');
        $this->dropForeignKey('favorite_ibfk_1', '{{%favorite}}');
        $this->dropForeignKey('board_post_ibfk_1', '{{%board_post}}');
        $this->dropForeignKey('reason_delete_ibfk_1', '{{%reason_delete}}');
        $this->dropForeignKey('post_ibfk_5', '{{%post}}');
        $this->dropForeignKey('wardrobe_ibfk_1', '{{%wardrobe}}');
        $this->dropForeignKey('viewed_post_ibfk_1', '{{%viewed_post}}');
        $this->dropForeignKey('post_tag_ibfk_1', '{{%post_tag}}');
        $this->dropForeignKey('post_image_ibfk_2', '{{%post_image}}');
        $this->dropForeignKey('category_ibfk_1', '{{%category}}');
    }
}
