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
	 * @var Request
	 */
	private $request;

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
		if ( ! $this->request) {
			/** @var RequestStack $requestStack */
			$requestStack = $this->get('request_stack');
			$this->request = $requestStack->getCurrentRequest();
		}

		return $this->request;
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


	public function getTemplatePath(string $view, ?string $controllerClass = null): string
	{
		if ( ! $controllerClass) {
			$controllerClass = $this->getExtendedController();
		}

		$reflector = new ReflectionClass($controllerClass);
		$templatesDirectoryName = str_replace('Controller', '', basename($reflector->getFileName(), '.php'));
		$templatesDirectoryNameLower = strtolower($templatesDirectoryName);
		$moduleDirectoryTemplatesPath =
			str_replace($this->getRootDirectory() . '/', '', dirname($reflector->getFileName()))
			. '/templates';

		$viewTemplate = $view . '.twig';
		$templatePath = $moduleDirectoryTemplatesPath . '/' . $viewTemplate;
		$kernelRootDir = $this->getParameter('kernel.root_dir');

		if ( ! file_exists($kernelRootDir . '/' . $templatePath)) {
			$templatePath = $moduleDirectoryTemplatesPath . '/' . $templatesDirectoryName . '/' . $viewTemplate;
		}

		if ( ! file_exists($kernelRootDir . '/' . $templatePath)) {
			$templatePath = $moduleDirectoryTemplatesPath . '/' . $templatesDirectoryNameLower . '/' . $viewTemplate;
		}

		if ( ! file_exists($kernelRootDir . '/' . $templatePath)) {
			$templatePath = $templatesDirectoryName . '/' . $viewTemplate;
		}

		if ( ! file_exists($this->getParameter('twig.default_path') . '/' . $templatePath)) {
			$templatePath = $templatesDirectoryNameLower . '/' . $viewTemplate;
		}

		return $templatePath;
	}


	/**
	 * @param mixed[] $parameters
	 */
	public function renderTemplate(array $parameters = [], ?Response $response = null): Response
	{
		preg_match('/\:\:render(?<template>\S+)/', $this->getRequest()->attributes->get('_controller'), $matches);

		$this->beforeRender();
		$this->setTemplateParameters($parameters);

		return $this->render(
			$this->getTemplatePath(strtolower($matches['template'])),
			$this->templateParameters,
			$response
		);
	}


	private function getExtendedController(): string
	{
		if ( ! $this->extendedController) {
			$this->extendedController = get_called_class();
		}

		return $this->extendedController;
	}

}
