<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OmniFlow;

/**
 * Description of ProcessExtensions
 *
 * @author ralph
 */
class CaseSampleTester extends CaseTester
{
    public function Test()
    {
        $this->StartNewCase("Expense Claim.bpmn");
        $this->AssertCaseCreated();
        
    }
}
class CaseTester 
 {
    var $case;
    /*
     * Verfies that the required case was created
     */
    public function StartNewCase($processName)
    {
        return true;
    }
    public function AssertCaseCreated()
    {
        return true;
    }
 }
 