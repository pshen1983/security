<?php
class GroupRuleHandler extends Handler {

	protected function handle($params) {
		$body = Utility::getJsonRequestData();

		$rules = $this->getValidator()->getRules();

		$failures = array();
		foreach ($rules as $rule) {
			if ($rule instanceof RuleThrottlingDao) {
				$subject = $body[$rule->getName()];
				$enforcher = new ThrottlingRuleEnforcer($rule, $subject);
			} 
			else if ($rule instanceof RuleGeoDao) {
				$subject = $body[$rule->getName()];
				$enforcher = new GeoRuleEnforcer($rule, $subject);
			} 
			else if ($rule instanceof RuleTokenDao) {
				$subject = $body[$rule->getName()];
				$enforcher = new TokenRuleEnforcer($rule, $subject);
			} 
			else if ($rule instanceof RuleBlacklistDao) {
				$subject = $body[$rule->getName()];
				$enforcher = new BlacklistRuleEnforcer($rule, $subject);
			}
			else if ($rule instanceof RuleWhitelistDao) {
				$subject = $body[$rule->getName()];
				$enforcher = new WhitelistRuleEnforcer($rule, $subject);
			}

			if (!$enforcher->enforce()) {
				$message = $enforcher->getErrorMessage();
				if (empty($message)) {
					$failures[$rule->getName()] = $rule->getErrorMessage();
				} else {
					$failures[$rule->getName()] = $message;
				}
			}
		}

		if (empty($failures)) {
			return array('status'=>'success');
		} else {
			return array('status'=>'failed', 'rules'=>$failures);
		}
	}
}
?>