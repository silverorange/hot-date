<?php

require_once 'HotDate/HotDateInterval.php';
require_once 'HotDate/HotDatePeriod.php';
require_once 'HotDate/HotDateTimeZone.php';

/**
 * PHP 5.2 to 5.3 adapter class for DateTime
 *
 * Supports the PHP 5.3 API as well as serialization.
 */
class HotDateTime implements Serializable
{
	protected $dateTime = null;

	public function __construct($time = 'now', DateTimeZone $timezone = null)
	{
		if ($timezone === null) {
			$this->dateTime = new DateTime($time);
		} else {
			$this->dateTime = new DateTime($time, $timezone);
		}
	}

	public function add(HotDateInterval $interval)
	{
		$modify = '';

		if ($interval->days) {
			$sign = ($interval->days > 0) ? '+' : '-';
			$modify .= ' ' . $sign . abs($interval->days) . ' days';
		} else {
			if ($interval->y) {
				$sign = ($interval->y > 0) ? '+' : '-';
				$modify .= ' ' . $sign . abs($interval->y) . ' years';
			}

			if ($interval->m) {
				$sign = ($interval->m > 0) ? '+' : '-';
				$modify .= ' ' . $sign . abs($interval->m) . ' months';
			}

			if ($interval->d) {
				$sign = ($interval->d > 0) ? '+' : '-';
				$modify .= ' ' . $sign . abs($interval->d) . ' days';
			}

			if ($interval->h) {
				$sign = ($interval->h > 0) ? '+' : '-';
				$modify .= ' ' . $sign . abs($interval->h) . ' hours';
			}

			if ($interval->i) {
				$sign = ($interval->i > 0) ? '+' : '-';
				$modify .= ' ' . $sign . abs($interval->i) . ' minutes';
			}

			if ($interval->s) {
				$sign = ($interval->s > 0) ? '+' : '-';
				$modify .= ' ' . $sign . abs($interval->s) . ' seconds';
			}
		}

		if ($interval->invert) {
			$modify = str_replace('-', '*', $modify);
			$modify = str_replace('+', '-', $modify);
			$modify = str_replace('*', '+', $modify);
		}

		$modify = trim($modify);

		return $this->modify($modify);
	}

	public function diff(DateTime $datetime2, $absolute = false)
	{
		$interval = new HotDateInterval();

		$seconds1 = $this->getTimestamp();
		$seconds2 = $datetime2->getTimestamp();

		if ($seconds1 < $seconds2 && $absolute) {
			$interval->invert = true;
		}

		return $interval;
	}

	public function format($format)
	{
		return $this->dateTime->format($format);
	}

	public function getOffset()
	{
		return $this->dateTime->getOffset();
	}

	public function getTimestamp()
	{
		return (integer)$this->dateTime->format('U');
	}

	public function getTimezone()
	{
		return $this->dateTime->getTimezone();
	}

	public function modify($modify)
	{
		if ($this->dateTime->modify($modify) === false) {
			return false;
		}
		return $this;
	}

	public function setDate($year, $month, $day)
	{
		if ($this->dateTime->setDate($year, $month, $day) === false) {
			return false;
		}
		return $this;
	}

	public function setISODate($year, $week, $day = 1)
	{
		if ($this->dateTime->setISODate($year, $week, $day) === false) {
			return false;
		}
		return $this;
	}

	public function setTime($hour, $minute, $second)
	{
		if ($this->dateTime->setTime($hour, $minute, $second) === false) {
			return false;
		}
		return $this;
	}

	public function setTimestamp($unixtimestamp)
	{
		$date = new HotDateTime('@' . $unixtimestamp, $this->getTimezone());

		$result = $this->dateTime->setDate(
			$date->format('Y'),
			$date->format('n'),
			$date->format('j')
		);

		if ($result !== false) {
			$result = $this->dateTime->setTime(
				$date->format('G'),
				$date->format('i'),
				$date->format('s') . '.' . $date->format('u')
			);
		}

		if ($result === false) {
			return false;
		}

		return $this;
	}

	public function setTimezone(DateTimeZone $timezone)
	{
		if ($this->dateTime->setTimezone($timezone) === false) {
			return false;
		}
		return $this;
	}

	public function sub(HotDateInterval $interval)
	{
		$interval = clone $interval;
		$interval->invert = (!$interval->invert);
		return $this->add($interval);
	}

	public function __toString()
	{
		return $this->format('c')
			. ' (' . $this->getTimezone()->getName() . ')';
	}

	public function serialize()
	{
		$data = array(
			$this->dateTime->format('U'),
			$this->dateTime->getTimezone()->getName(),
		);

		return serialize($data);
	}

	public function unserialize($serialized)
	{
		$data = unserialize($serialized);
		$this->dateTime = new DateTime(
			'@' . $data[0],
			new HotDateTimeZone($data[1])
		);
	}

}

?>