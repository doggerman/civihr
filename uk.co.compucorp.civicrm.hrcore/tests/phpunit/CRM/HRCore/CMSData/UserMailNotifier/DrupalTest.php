<?php

use CRM_HRCore_CMSData_UserMailNotifier_Drupal as DrupalUserMailNotifier;
/**
 * Class CRM_HRCore_CMSData_UserMailNotifier_DrupalTest
 *
 * @group headless
 */
class CRM_HRCore_CMSData_UserMailNotifier_DrupalTest extends CRM_HRCore_Test_BaseHeadlessTest {

  public function testUserNotifyIsCalledForPasswordResetEmail() {
    $contactData = ['cmsId' => 1];
    $userMailNotifier = new DrupalUserMailNotifier();
    $user = $userMailNotifier->getUser($contactData);
    $result = $userMailNotifier->sendPasswordResetEmail($user);
    $expectedOperation = 'password_reset';
    $this->assertEquals($expectedOperation, $result['operation']);
    $this->assertInstanceOf(stdClass::class, $result['user']);
  }

  public function testUserNotifyIsCalledForWelcomeEmail() {
    $contactData = ['cmsId' => 1];
    $userMailNotifier = new DrupalUserMailNotifier();
    $user = $userMailNotifier->getUser($contactData);
    $result = $userMailNotifier->sendWelcomeEmail($user);
    $expectedOperation = 'register_admin_created';
    $this->assertEquals($expectedOperation, $result['operation']);
    $this->assertInstanceOf(stdClass::class, $result['user']);
  }

  public function testGetUserReturnsAnObject() {
    $contactData = ['cmsId' => 1];
    $userMailNotifier = new DrupalUserMailNotifier();
    $user = $userMailNotifier->getUser($contactData);
    $this->assertInstanceOf(StdClass::class, $user);
  }
}
