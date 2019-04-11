<?php

/**
 *
 * Copyright (c) Vladimír Macháček (email@machy8.com)
 *
 * For the full copyright and license information, please view the file license.md
 * that was distributed with this source code.
 *
 */

declare(strict_types = 1);

namespace Machy8\SmartController;

use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;


abstract class SmartController extends AbstractController
{

	/**
	 * @var string
	 */
	private $extendedController;

	/**
	 * @var string
	 */
	private $rootDirectory;

	/**
	 * @var mixed[]
	 */
	private $templateParameters = [];


	public function beforeRender(): void
	{
	}


	public function getRequest(): ?Request
	{
		/** @var RequestStack $requestStack */
		$requestStack = $this->get('request_stack');
		return $requestStack->getCurrentRequest();
	}


	public function getRootDirectory(): string
	{
		if ( ! $this->rootDirectory) {
			$this->setRootDirectory($this->getParameter('kernel.root_dir'));
		}

		return $this->rootDirectory;
	}


	public function setRootDirectory(string $directory): SmartController
	{
		$this->rootDirectory = $directory;
		return $this;
	}


	/**
	 * @return mixed|null
	 */
	public function getTemplateParameter(string $name)
	{
		return $this->templateParameterExists($name) ? $this->templateParameters[$name] : null;
	}


	/**
	 * @param mixed[] $parameters
	 */
	public function setTemplateParameters(array $parameters): SmartController
	{
		$this->templateParameters = array_merge($this->templateParameters, $parameters);
		return $this;
	}


	public function templateParameterExists(string $name): bool
	{
		return array_key_exists($name, $this->templateParameters);
	}


	/**
	 * @param mixed[] $parameters
	 */
	public function renderTemplate(string $templatePath, array $parameters = [], ?Response $response = null): Response
	{
		preg_match('/\:\:render(?<template>\S+)/', $this->getRequest()->attributes->get('_controller'), $matches);

		$this->beforeRender();
		$this->setTemplateParameters($parameters);

		return $this->render($templatePath, $this->templateParameters, $response);
	}

}
