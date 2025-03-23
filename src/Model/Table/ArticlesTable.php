<?php

declare(strict_types=1);

namespace App\Model\Table;

use Cake\Event\EventInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\Utility\Text;
use Cake\Validation\Validator;

class ArticlesTable extends Table
{
	public function initialize(array $config): void
	{
		$this->addBehavior('Timestamp');
		$this->belongsToMany('Tags', [
			'joinTable' => 'articles_tags',
			'dependent' => true,
		]);
	}

	public function beforeSave(EventInterface $event, $entity, $options)
	{
		if ($entity->tag_string) {
			$entity->tags = $this->_buildTags($entity->tag_string);
		}

		if ($entity->isNew() && !$entity->slug) {
			$sluggedTitle = Text::slug($entity->title);
			// trim slug to maximum length defined in schema
			$entity->slug = substr($sluggedTitle, 0, 191);
		}
	}

	public function _buildTags($tagString)
	{
		$newTags = array_map('trim', explode(',', $tagString));
		// Remove all empty tags
		$newTags = array_filter($newTags);
		// Reduce duplicated tags
		$newTags = array_unique($newTags);

		$out = [];
		$tags = $this->Tags->find()
			->where(['Tags.title IN' => $newTags])
			->all();

		// Remove existing tags from the list of new tags.
		foreach ($tags as $tag) {
			$index = array_search($tag->title, $newTags);
			if ($index !== false) {
				unset($newTags[$index]);
			}
		}
		// Add existing tags to the list of tags.
		foreach ($tags as $tag) {
			$out[] = $tag;
		}
		// Add new tags.
		foreach ($newTags as $tag) {
			$out[] = $this->Tags->newEntity(['title' => $tag]);
		}
		return $out;
	}

	public function validationDefault(Validator $validator): Validator
	{
		$validator
			->notEmptyString('title')
			->minLength('title', 10)
			->maxLength('title', 255)

			->notEmptyString('body')
			->minLength('body', 10);

		return $validator;
	}

	public function findTagged(SelectQuery $query, array $tags = [])
	{
		$columns = ['Articles.id', 'Articles.user_id', 'Articles.title', 'Articles.body', 'Articles.published', 'Articles.created', 'Articles.slug'];

		$query = $query
			->select($columns)
			->distinct($columns);

		if (empty($tags)) {
			$query->leftJoinWith('Tags')
				->where(['Tags.title IS' => null]);
		} else {
			$query->innerJoinWith('Tags')
				->where(['Tags.title IN' => $tags]);
		}
		return $query->groupBy(['Articles.id']);
	}
}
