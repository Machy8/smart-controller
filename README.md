[![Build Status](https://travis-ci.org/Machy8/smart-controller.svg?branch=master)](https://travis-ci.org/Machy8/smart-controller)

# Smart Controller
Summary
- Before render method - allows you to set parameters you always need
- Template parameters - can be set from multiple places easily
- Templates autodiscovery - you don't need to write the template path anymore
- Usefull methods - getRequest(), getTemplatePath(), getRootDirectory(), getTemplateParameter()

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

}
```

and now with the **Smart Controller**.

```php
class LuckyController extends SmartController
{

	/**
	 * @Route("/lucky/number")
	 */
	public function renderNumber(): response
	{
		return $this->renderTemplate([
			'number' => random_int(0, 100)
		]);
	}

}
```

What if we use the **beforeRender** method? The number parameter will be set into both render methods :-)

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
	public function renderLuckyNumber(): response
	{
		return $this->renderTemplate();
	}


    /**
     * @Route("/unlucky/number")
     */
    public function renderUnluckyNumber(): response
    {
        return $this->renderTemplate();
    }

}
```

## Notes
- Every method where renderTemplate is called must start with the prefix `render*` => `renderNumber()`, `renderHomepage()`
- Templates are searched in the following order: **structure** => **example path**
```
<module name>/templates/template.twig => src/Controller/templates/template.twig
<module name>/templates/<controller name>/template.twig => src/Controller/templates/Lucky/template.twig
<module name>/templates/<controller name (lowercase)>/template.twig => src/Controller/templates/lucky/template.twig
<twig default path>/<controller name>/template.twig => templates/Lucky/template.twig
<twig default path>/<controller name (lowercase)>/template.twig => templates/lucky/template.twig
```
- The whole paths can be even more nested: **Controller path** => **template path**
```
src/Modules/HomepageModule/FrontModule/Controller/LuckyController.php => src/Modules/HomepageModule/FrontModule/Controller/templates/Lucky/template.twig
src/Modules/HomepageModule/AdminModule/Controller/LuckyController.php => src/Modules/HomepageModule/AdminModule/Controller/templates/template.twig
```
