<?php
/**
 * Minical plugin for Craft CMS 3.x
 *
 * Show entries in a calendar layout
 *
 * @link      https://www.disposition.tools
 * @copyright Copyright (c) 2022 Disposition Tools
 */

namespace dispositiontools\minical\variables;

use dispositiontools\minical\Minical;
use dispositiontools\minical\services\Layout as LayoutService;
use Craft;

/**
 * Minical Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.minical }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    Disposition Tools
 * @package   Minical
 * @since     1.0.0
 */
class MinicalVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.minical.cal }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.minical.cal(calOptions) }}
     *
     * @param null $optional
     * @return string
     */
    public function cal($calOptions = null): ?array
    {

      $returnData =  LayoutService::createCalendarLayout($calOptions);

      return $returnData ;


    }
}
