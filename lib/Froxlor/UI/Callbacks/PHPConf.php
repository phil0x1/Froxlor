<?php

namespace Froxlor\UI\Callbacks;

use Froxlor\Idna\IdnaWrapper;
use Froxlor\UI\Panel\UI;

/**
 * This file is part of the Froxlor project.
 * Copyright (c) 2010 the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @package    Froxlor\UI\Callbacks
 *
 */
class PHPConf
{
	public static function domainList(array $attributes): string
	{
		$idna = new IdnaWrapper;
		$domains = "";
		$subdomains_count = count($attributes['fields']['subdomains']);
		foreach ($attributes['fields']['domains'] as $configdomain) {
			$domains .= $idna->decode($configdomain) . "<br>";
		}
		if ($subdomains_count == 0 && empty($domains)) {
			$domains = UI::getLng('admin.phpsettings.notused');
		} else {
			$domains .= !empty($subdomains_count) ? ((!empty($domains) ? '+ ' : '') . $subdomains_count . ' ' . UI::getLng('customer.subdomains')) : '';
		}

		return $domains;
	}

	public static function configsList(array $attributes)
	{
		$configs = "";
		foreach ($attributes['fields']['configs'] as $configused) {
			$configs .= $configused . "<br>";
		}
		return $configs;
	}

	public static function isNotDefault(array $attributes)
	{
		if (UI::getCurrentUser()['change_serversettings']) {
			return $attributes['fields']['id'] != 1;
		}
		return false;
	}

	public static function fpmConfLink(array $attributes)
	{
		if (UI::getCurrentUser()['change_serversettings']) {
			$linker = UI::getLinker();
			return [
				'macro' => 'link',
				'data' => [
					'text' => $attributes['data'],
					'href' => $linker->getLink([
						'section' => 'phpsettings',
						'page' => 'fpmdaemons',
						'action' => 'edit',
						'id' => $attributes['fields']['fpmsettingid'],
					]),
				]
			];
		}
		return $attributes['data'];
	}
}
