<?php
/**
 * Minical plugin for Craft CMS 3.x
 *
 * Show entries in a calendar layout
 *
 * @link      https://www.disposition.tools
 * @copyright Copyright (c) 2022 Disposition Tools
 */

namespace dispositiontools\minical\services;

use dispositiontools\minical\Minical;
use craft\helpers\DateTimeHelper;

use Craft;
use craft\base\Component;

/**
 * Layout Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Disposition Tools
 * @package   Minical
 * @since     1.0.0
 */
class Layout extends Component
{
    // Public Methods
    // =========================================================================


    //  Minical::$plugin->layout->createCalendarLayout($options);
    public static function createCalendarLayout($options= null): ?array
    {

        $now = DateTimeHelper::toDateTime('now');
        // set up defaults
        $startDate = DateTimeHelper::toDateTime($now->format('Y-m-1'));
        $endDate = DateTimeHelper::toDateTime($now->format('Y-m-t'));

        $elements = false;

        $firstDayOfTheWeek = 1;
        $dateField = "postDate";
        $dayTitleFormat = "D";

        $elementsByDay = [];



        // over write defaults with options if they exist
        if(is_array($options))
        {



            if( array_key_exists('firstDayOfTheWeek',$options) )
            {
                  $firstDayOfTheWeek = $options['firstDayOfTheWeek'];
            }

            if( array_key_exists('dayTitleFormat',$options) )
            {
                  $dayTitleFormat = $options['dayTitleFormat'];
            }

            if( array_key_exists('dateField',$options) )
            {
                  $dateField = $options['dateField'];
            }

            if(array_key_exists('elements',$options))
            {
                // if there are elements then lets get the first date and last date from there.
                $elements = $options['elements'];

                $startDate = DateTimeHelper::toDateTime('now');
                $endDate = DateTimeHelper::toDateTime('now');

                foreach( $elements as $element )
                {

                    // get the first and last date of the elements
                    if( $startDate > $element->$dateField )
                    {
                        $startDate = $element->$dateField;
                    }

                    if( $endDate < $element->$dateField )
                    {
                        $endDate = $element->$dateField;
                    }

                    // create an array of elements by date for inserting in to the days later.
                    $elementsByDay[$element->$dateField->format('Y-m-d')][] = $element;

                }

            }


            if( array_key_exists('startDateString',$options)  )
            {
                  $startDate = DateTimeHelper::toDateTime($options['startDateString']);
            }

            if( array_key_exists('endDateString',$options)  )
            {
                  $endDate = DateTimeHelper::toDateTime($options['endDateString']);
            }


        }

        // calculate the number of months between start date and end date

        $startCalDate = DateTimeHelper::toDateTime($startDate->format('Y-m-1'));
        $endCalDate = DateTimeHelper::toDateTime($endDate->format('Y-m-t'));


        $interval = $startCalDate->diff($endCalDate);

        $numberOfMonths  = ( $interval->y * 12 ) + $interval->m;


        $months = [];

        // create the day headers
        $dayHeaders = array();
      	for($n=0,$t=(3+$firstDayOfTheWeek)*86400; $n<7; $n++,$t+=86400)
        {
            	//$dayHeaders[$n] = ucfirst(gmstrftime('%A',$t)); #%A means full textual day name
                $dayHeaderDate = DateTimeHelper::toDateTime( $t );
              	$dayHeaders[$n] = $dayHeaderDate->format($dayTitleFormat);
        }



        $monthStartDate = DateTimeHelper::toDateTime( $startDate->format('Y-m-1') );

        for($monthCount = 0; $monthCount <= $numberOfMonths; $monthCount++ )
        {


            $monthDayDate = DateTimeHelper::toDateTime( $monthStartDate->format('Y-m-d') );

            // get the current day of the week
            $currentDayOfTheWeek = $monthDayDate->format('w');

            // correct for first day of the week setting
            $currentDayOfTheWeek = ($currentDayOfTheWeek + 7 - $firstDayOfTheWeek) % 7;

              $weekNumber = 0;
              $weekDetails = [];
              $days = [];
              $dayDetailsBlank = [
                'blank' => true,
                'elements' => false,
                'dayDate' => false,
                'hasElements' => false,
                'numberOfElements' => 0
              ];

            if($currentDayOfTheWeek > 0)
            {
              for($blankDays = 1; $blankDays <= $currentDayOfTheWeek; $blankDays++)
              {
                $days[]= $dayDetailsBlank;
              }

            }

            $daysInMonth=$monthStartDate->format('t');

          	for($day=1; $day<=$daysInMonth; $day++,$currentDayOfTheWeek++)
            {

              		if($currentDayOfTheWeek == 7)
                  {
              			$currentDayOfTheWeek   = 0; #start a new week
                    $weekDetails[]= ['days' => $days];
                    $days=[];
              		}

                  $dayData = [
                    'dayDate' => DateTimeHelper::toDateTime( $monthDayDate->format('Y-m-d') ),
                    'blank' => false,
                    'elements' => false,
                    'hasElements' => false,
                    'numberOfElements' => 0
                  ];

                  if (array_key_exists($monthDayDate->format('Y-m-d'),$elementsByDay ))
                  {
                    $dayData['elements'] = $elementsByDay[$monthDayDate->format('Y-m-d')];
                    $dayData['hasElements'] = true;
                    $dayData['numberOfElements'] = count( $elementsByDay[$monthDayDate->format('Y-m-d')] );
                  }




                  $days[]=$dayData;
                  $monthDayDate->modify("+1day" );
          	} // end for days



              // add any extra blank days
                for($blankDays = 1; $blankDays < 7 - $currentDayOfTheWeek; $blankDays++)
                {
                  $days[]= $dayDetailsBlank;
                }

                // add them to the month weeks
                $weekDetails[]= ['days' => $days];




            //$weeks = Layout::calculateMonth($monthStartDate, $elements);
            $month = [
              'monthStartDate' =>  DateTimeHelper::toDateTime( $monthStartDate->format('Y-m-d') ),
              'weeks' => $weekDetails
            ];

            $months[] = $month;

            $monthStartDate->modify("+1 month");
        } // end for months


        $returnData = [
          'startDate' => $startDate,
          'endDate' => $endDate,
          'months' => $months,
          'numberOfMonths' => $numberOfMonths,
          'dayHeaders' => $dayHeaders,
        ];

        return $returnData;
    }


}
