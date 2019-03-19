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


	public function getTemplatePath(string $view, ?string $controllerClass = null): string
	{
		if ( ! $controllerClass) {
			$controllerClass = $this->getExtendedController();
		}

		$reflector = new ReflectionClass($controllerClass);
		$controllerName = lcfirst(str_replace('Controller', '', basename($reflector->getFileName(), '.php')));
		$moduleTemplatesDirectoryPath =
			str_replace($this->getRootDirectory() . '/', '', dirname($reflector->getFileName())) . '/templates';
		$twigDefaultPath = $this->getParameter('twig.default_path');
		$kernelRootDir = $this->getParameter('kernel.root_dir');
		$templateName = $view . '.twig';
		$templatePath = $templateName;
		$templatePathOptions = [
			$controllerName . '/' . $templateName => $twigDefaultPath,
			$moduleTemplatesDirectoryPath . '/' . $templateName => $kernelRootDir,
			$moduleTemplatesDirectoryPath . '/' . $controllerName . '/' . $templateName => $kernelRootDir
		];

		if ( ! file_exists($twigDefaultPath . '/' . $templatePath)) {
			foreach ($templatePathOptions as $templateRelativePath => $templateDirectory) {
				if (file_exists($templateDirectory . '/' . $templateRelativePath)) {
					$templatePath = $templateRelativePath;
					break;
				}
			}
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
