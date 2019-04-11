[![Build Status](https://travis-ci.org/Machy8/smart-controller.svg?branch=master)](https://travis-ci.org/Machy8/smart-controller)

# Smart Controller
Based on the article [Symfony 4: Creating Smart Controller](https://machy8.com/blog/symfony-4-creating-smart-controller). Summary:
- Before render method - allows you to set parameters you always need
- Template parameters - can be set from multiple places easily
- Usefull methods - getRequest(), getRootDirectory(), getTemplateParameter()

## Installation
```
composer require machy8/smart-controller
```

## Example
Symfony original lucky controller [example](https://symfony.com/doc/current/page_creation.html#creating-a-page-route-and-controller).

```php
class LuckyController extends AbstractController
{

	/**
	 * @Route("/lucky/number")
	 */
	public function number(): Response
	{
		return $this->render('lucky/number.twig', [
			'number' => random_int(0, 100),
		]);
	}


	/**
	 * @Route("/unlucky/number")
	 */
	public function unluckyNumber(): Response
	{
		return $this->render('lucky/number.twig', [
			'number' => random_int(0, 100),
		]);
	}

}
```

and now with the **Smart Controller**.

```php
class LuckyController extends SmartController
{

    public function beforeRender(): void
    {
        $this->setTemplateParameters([
            'number' => random_int(0, 100)
        ]);
    }

    /**
     * @Route("/lucky/number")
     */
    public function renderLuckyNumber(): Response
    {
        return $this->renderTemplate('lucky/number.twig');
    }

    /**
     * @Route("/unlucky/number")
     */
    public function renderUnluckyNumber(): Response
    {
        return $this->renderTemplate('lucky/number.twig');
    }

}
```
