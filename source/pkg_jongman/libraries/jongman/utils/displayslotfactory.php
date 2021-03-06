<?php
defined('_JEXEC') or die;

/**
 * 
 * Display slot based on slot type, can be overriden in view class
 *
 */
class RFDisplaySlotFactory
{
	/**
	 * 
	 * This method can be override in the view
	 * @param unknown_type $slot
	 * @param unknown_type $slotRef
	 * @param unknown_type $href
	 */
	protected function displayMyReserved(IReservationSlot $slot, $slotRef, $href) 
	{
		$html 	= array();
		if ($slot->isPending()) {
			$class ='pending';	
		}else{
			$class = '';
		}
		$html[] = "<td colspan=\"{$slot->periodSpan()}\" class=\"reserved {$class} mine clickres slot\" resid=\"{$slot->getInstanceId()}\">";
		$html[] = $slot->label();
		$html[] = "</td>";
		
		return implode("", $html);			
	} 
	
	protected function displayReserved(IReservationSlot $slot, $slotRef, $href) 
	{
		$html 	= array();
		if ($slot->isPending()) {
			$class ='pending';	
		}else{
			$class = '';
		}
		$html[] = "<td colspan=\"{$slot->periodSpan()}\" class=\"reserved {$class} clickres slot\" resid=\"{$slot->getInstanceId()}\">";
		$html[] = "</td>";
		
		return implode("", $html);		
	}

	protected function displayMyParticipating(IReservationSlot $slot, $slotRef, $href) 
	{
		$html 	= array();
		if ($slot->isPending()) {
			$class ='pending';	
		}else{
			$class = '';
		}
		$html[] = "<td colspan=\"{$slot->periodSpan()}\" class=\"reserved {$class} participating clickres slot\">";
		$html[] = "</td>";
		
		return implode("", $html);			
	}

	protected function displayPastTime(IReservationSlot $slot, $slotRef, $href) 
	{
		$html 	= array();
		$html[] = "<td ref=\"{$slotRef}\" colspan=\"{$slot->periodSpan()}\" class=\"pasttime slot\">";
		$html[] = "{$slot->label()}";
		$html[] = "</td>";
		
		return implode("", $html);		
	}
	
	protected function displayRestricted(IReservationSlot $slot, $slotRef, $href) 
	{
		$html 	= array();
		$html[] = "<td colspan=\"{$slot->periodSpan()}\" class=\"stricted slot\">";
		$html[] = "</td>";
		
		return implode("", $html);		
	} 
	
	protected function displayReservable(IReservationSlot $slot, $slotRef, $href) 
	{
		$html 	= array();
		$html[] = "<td ref=\"{$slotRef}\" colspan=\"{$slot->periodSpan()}\" class=\"reservable clickres slot\">";
		$html[] = "<input type=\"hidden\" class=\"href\" value=\"{$href}\">";
		$html[] = "<input type=\"hidden\" class=\"start\" value=\"{$slot->beginDate()->format('Y-m-d H:i:s')}\">";
		$html[] = "<input type=\"hidden\" class=\"end\" value=\"{$slot->endDate()->format('Y-m-d H:i:s')}\">";
 		$html[] = "</td>";
		
		return implode("", $html);		
	} 
	 
	protected function displayUnreservable(IReservationSlot $slot, $slotRef, $href) 
	{
		$html 	= array();
		$html[] = "<td colspan=\"{$slot->periodSpan()}\" class=\"unreservable slot\">";
		$html[] = "</td>";
		
		return implode("", $html);
	}

	/**
	 * 
	 * Display schedule reservation slot on view layout
	 * @param IReservationSlot $slot
	 * @param unknown_type $slotRef
	 * @param unknown_type $accessAllowed
	 * @param unknown_type $view
	 */
	public function display(IReservationSlot $slot, $slotRef, $href, $accessAllowed = false, $view=null)
	{
		$method = self::getFunction($slot, $accessAllowed);
		if ($method) {
			if ($view !== null && is_object($view) && method_exists($view, $method)) {
				return call_user_func_array(array($method, $view), array($slot, $slotRef, $href));	
			}else{
				return call_user_func_array(array(__CLASS__, $method), array($slot, $slotRef, $href));
			}		
		}else{
			return '<td class="slot"><td>';	
		}	
	}
	
	protected function getFunction(IReservationSlot $slot, $accessAllowed = false)
	{
		$slot->isPending();
		if ($slot->isReserved())
		{
			if (self::isMyReservation($slot))
			{
				return 'displayMyReserved';
			}
			elseif (self::amIParticipating($slot))
			{
				return 'displayMyParticipating';
			}
			else{
				return 'displayReserved';
			}
		}
		else
		{
			if (!$accessAllowed)
			{
				return 'displayRestricted';
			}
			else
			{
				if ($slot->isPastDate(RFDate::now()) && !self::userHasAdminRights($slot))
				{
					return 'displayPastTime';
				}
				else
				{
					if ($slot->isReservable())
					{
						return 'displayReservable';
					}
					else
					{
						return 'displayUnreservable';
					}
				}
			}
		}

		return null;
	}

	private function userHasAdminRights(IReservationSlot $slot)
	{
		return true;
	}

	private function isMyReservation(IReservationSlot $slot)
	{
		$user = JFactory::getUser();
		return $slot->isOwnedBy($user);
	}

	private function amIParticipating(IReservationSlot $slot)
	{
		$user = JFactory::getUser();
		return $slot->isParticipating($user);
	}
}