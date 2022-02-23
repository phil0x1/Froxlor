<?php

namespace Froxlor\UI\Callbacks;

use Froxlor\PhpHelper;
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
 * @author     Maurice Preuß <hello@envoyr.com>
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @package    Listing
 *
 */
class ProgressBar
{
    /**
     * get progressbar data for used diskspace
     *
     * @param string $data
     * @param array $attributes
     * @return array
     */
	public static function diskspace(string $data, array $attributes): array
	{
		$infotext = '';
		if (isset($attributes['customerid'])) {
			// get disk-space usages for web, mysql and mail
			$usages_stmt = \Froxlor\Database\Database::prepare("
				SELECT * FROM `" . TABLE_PANEL_DISKSPACE . "`
				WHERE `customerid` = :cid
				ORDER BY `stamp` DESC LIMIT 1
			");
			$usages = \Froxlor\Database\Database::pexecute_first($usages_stmt, array(
				'cid' => $attributes['customerid']
			));

			if ($usages != true) {
				$usages = [
					'webspace' => 0,
					'mailspace' => 0,
					'dbspace' => 0
				];
			}

			$infotext = UI::getLng('panel.used') . ':<br>';
			$infotext .= 'web: ' . PhpHelper::sizeReadable($usages['webspace'] * 1024, null, 'bi') . '<br>';
			$infotext .= 'mail: ' . PhpHelper::sizeReadable($usages['mailspace'] * 1024, null, 'bi') . '<br>';
			$infotext .= 'mysql: ' . PhpHelper::sizeReadable($usages['dbspace'] * 1024, null, 'bi');
		}

		return self::pbData('diskspace', $attributes, 1024, (int)\Froxlor\Settings::Get('system.report_webmax'), $infotext);
    }

    /**
     * get progressbar data for traffic
     *
     * @param string $data
     * @param array $attributes
     * @return array
     */
	public static function traffic(string $data, array $attributes): array
	{
		return self::pbData('traffic', $attributes, 1024 * 1024, (int)\Froxlor\Settings::Get('system.report_trafficmax'));
    }

	/**
	 * do needed calculations
	 */
	private static function pbData(string $field, array $attributes, int $size_factor = 1024, int $report_max = 90, $infotext = null): array
	{
		$percent = 0;
		$style = 'bg-info';
		$text = PhpHelper::sizeReadable($attributes[$field . '_used'] * $size_factor, null, 'bi') . ' / ' . UI::getLng('customer.unlimited');
		if ((int) $attributes[$field] >= 0) {
			if (($attributes[$field] / 100) * $report_max < $attributes[$field . '_used']) {
				$style = 'bg-danger';
			} elseif (($attributes[$field] / 100) * ($report_max - 15) < $attributes[$field . '_used']) {
				$style = 'bg-warning';
			}
			$percent = round(($attributes[$field . '_used'] * 100) / ($attributes[$field] == 0 ? 1 : $attributes[$field]), 0);
			if ($percent > 100) {
				$percent = 100;
			}
			$text = PhpHelper::sizeReadable($attributes[$field . '_used'] * $size_factor, null, 'bi') . ' / ' . PhpHelper::sizeReadable($attributes[$field] * $size_factor, null, 'bi');
		}

		return [
            'type' => 'progressbar',
            'data' => [
                'percent' => $percent,
                'style' => $style,
                'text' => $text,
                'infotext' => $infotext
            ]
        ];
	}
}
