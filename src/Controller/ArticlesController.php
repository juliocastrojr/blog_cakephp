<?php

namespace App\Controller;

class ArticlesController extends AppController
{
	public function index()
	{
		$articles = $this->paginate($this->Articles);
		$this->set(compact('articles'));
	}

	public function view($slug = null)
	{
		$article = $this->Articles
			->findBySlug($slug)
			->contain('Tags')
			->firstOrFail();
		$this->set(compact('article'));
	}

	public function add()
	{
		$article = $this->Articles->newEmptyEntity();
		if ($this->request->is('post')) {
			$article = $this->Articles->patchEntity($article, $this->request->getData());

			// $article->user_id = $this->request->getAttribute('identity')->getIdentifier();
			$article->user_id = 1;

			if ($this->Articles->save($article)) {
				$this->Flash->success(__('Your article has been saved.'));
				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('Unable to add your article.'));
		}
		$tags = $this->Articles->Tags->find('list')->all();
		$this->set('tags', $tags);
		$this->set('article', $article);
	}

	public function edit($slug)
	{
		$article = $this->Articles
			->findBySlug($slug)
			->contain('Tags')
			->firstOrFail();

		if ($this->request->is(['post', 'put'])) {
			$this->Articles->patchEntity($article, $this->request->getData());
			if ($this->Articles->save($article)) {
				$this->Flash->success(__('Your article has been updated.'));
				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('Unable to update your article.'));
		}

		$tags = $this->Articles->Tags->find('list')->all();

		$this->set('tags', $tags);

		$this->set('article', $article);
	}

	public function delete($slug)
	{
		$this->request->allowMethod(['post', 'delete']);

		$article = $this->Articles->findBySlug($slug)->firstOrFail();
		if ($this->Articles->delete($article)) {
			$this->Flash->success(__('The {0} article has been deleted.', $article->title));
			return $this->redirect(['action' => 'index']);
		}
	}

	public function tags(...$tags)
	{
		$articles = $this->Articles->find('tagged', tags: $tags)->all();

		$this->set([
			'articles' => $articles,
			'tags' => $tags
		]);
	}
}
