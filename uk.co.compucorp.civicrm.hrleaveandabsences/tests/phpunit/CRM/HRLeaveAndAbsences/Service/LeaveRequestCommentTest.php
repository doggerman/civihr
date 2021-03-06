<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRComments_Test_Fabricator_Comment as CommentFabricator;
use CRM_HRComments_BAO_Comment as Comment;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestComment as LeaveRequestCommentService;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestCommentTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestCommentTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;

  private $leaveRequestCommentService;

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");

    $this->leaveRequestCommentService = new LeaveRequestCommentService();
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 1;");
  }

  public function testAddCanCreateCommentForLeaveRequest() {
    $params = [
      'leave_request_id' => 1,
      'text' => 'Random Commenter',
      'contact_id' => 1,
      'sequential' => 1
    ];

    $result = $this->leaveRequestCommentService->add($params);

    $comment = new Comment();
    $comment->find();
    $this->assertEquals(1, $comment->N);
    $comment->fetch();

    $date = new DateTime($comment->created_at);

    $expected = [
      [
        'comment_id' => $comment->id,
        'leave_request_id' => $comment->entity_id,
        'text' => $comment->text,
        'contact_id' => $comment->contact_id,
        "created_at"=> $date->format('YmdHis')
      ]
    ];

    $this->assertEquals($expected, $result['values']);
  }

  public function testAddCanCreateCommentForLeaveRequestWhenCreatedAtIsPartOfTheParametersPassed() {
    $created_at = new DateTime('2016-10-10 09:20:43');
    $params = [
      'leave_request_id' => 1,
      'text' => 'Random Commenter',
      'contact_id' => 1,
      'created_at' => $created_at->format('Y-m-d H:i:s'),
      'sequential' => 1
    ];

    $result = $this->leaveRequestCommentService->add($params);

    $comment = new Comment();
    $comment->find();
    $this->assertEquals(1, $comment->N);
    $comment->fetch();

    $expected = [
      [
        'comment_id' => $comment->id,
        'leave_request_id' => $comment->entity_id,
        'text' => $comment->text,
        'contact_id' => $comment->contact_id,
        'created_at'=> $created_at->format('YmdHis')
      ]
    ];

    $this->assertEquals($expected, $result['values']);
  }

  public function testAddCannotUpdateCommentForLeaveRequest() {
    $params = [
      'leave_request_id' => 1,
      'text' => 'Random Commenter',
      'contact_id' => 1,
      'sequential' => 1
    ];

    $result = $this->leaveRequestCommentService->add($params);

    $comment = new Comment();
    $comment->find();
    $this->assertEquals(1, $comment->N);
    $comment->fetch();

    $date = new DateTime($comment->created_at);

    $expected = [
      [
        'comment_id' => $comment->id,
        'leave_request_id' => $comment->entity_id,
        'text' => $comment->text,
        'contact_id' => $comment->contact_id,
        "created_at"=> $date->format('YmdHis')
      ]
    ];

    $this->assertEquals($expected, $result['values']);

    //update comment
    $updateParams = [
      'comment_id' => $comment->id,
      'leave_request_id' => 1,
      'text' => 'Test Commenter',
      'contact_id' => 2,
      'sequential' => 1
    ];

    $this->setExpectedException('UnexpectedValueException', 'You cannot update a comment!');
    $this->leaveRequestCommentService->add($updateParams);
  }

  public function testGetReturnsAssociatedCommentsForLeaveRequest() {
    $entityName = 'LeaveRequest';
    $comment1 = CommentFabricator::fabricate([
      'entity_id' => 1,
      'entity_name' => $entityName,
      'contact_id' => 1,
    ]);

    $comment2 = CommentFabricator::fabricate([
      'entity_id' => 1,
      'entity_name' => $entityName,
      'contact_id' => 1,
    ]);

    $comment3 = CommentFabricator::fabricate([
      'entity_id' => 3,
      'entity_name' => $entityName,
      'contact_id' => 1,
    ]);

    $result = $this->leaveRequestCommentService->get(['leave_request_id' => 1, 'sequential' => 1]);

    $comment1Date = new DateTime($comment1->created_at);
    $comment2Date = new DateTime($comment2->created_at);

    $expected1 = [
      [
        'comment_id' => $comment1->id,
        'leave_request_id' => $comment1->entity_id,
        'text' => $comment1->text,
        'contact_id' => $comment1->contact_id,
        'created_at' => $comment1Date->format('Y-m-d H:i:s')
      ],
      [
        'comment_id' => $comment2->id,
        'leave_request_id' => $comment2->entity_id,
        'text' => $comment2->text,
        'contact_id' => $comment2->contact_id,
        'created_at' => $comment2Date->format('Y-m-d H:i:s')
      ]
    ];

    $this->assertEquals($expected1, $result['values']);
  }

  public function testGetReturnsOnlyCommentsOfLeaveRequestsOfTheCurrentLoggedInUserWhenTheLoggedInUserIsAStaffMember() {
    $entityName = 'LeaveRequest';

    $contact1 = ['id' => 1];
    $contact2 = ['id' => 2];

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact1['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact2['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $comment1 = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest1->id,
      'entity_name' => $entityName,
      'contact_id' => $leaveRequest1->contact_id,
    ]);

    $comment2 = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest1->id,
      'entity_name' => $entityName,
      'contact_id' => $contact2['id'],
    ]);

    CommentFabricator::fabricate([
      'entity_id' => $leaveRequest2->id,
      'entity_name' => $entityName,
      'contact_id' => $contact1['id'],
    ]);

    // Register contact1 in session and make sure that only the permission to use
    // the API is set (to ensure the contact won't be considered an admin)
    $this->registerCurrentLoggedInContactInSession($contact1['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access AJAX API'];

    // LeaveRequest 1 belongs to the current logged in user, so all the comments
    // linked to it will be returned (even those linked to a different contact)
    $result = $this->leaveRequestCommentService->get([
      'leave_request_id' => $leaveRequest1->id,
      'check_permissions' => true
    ]);
    $this->assertCount(2, $result['values']);
    $this->assertNotEmpty($result['values'][$comment1->id]);
    $this->assertNotEmpty($result['values'][$comment2->id]);

    // LeaveRequest 2 does not belong to the current logged in user, so nothing
    // will be returned (even if the only comment of that leave request belongs
    // to the logged in user)
    $result = $this->leaveRequestCommentService->get([
      'leave_request_id' => $leaveRequest2->id,
      'check_permissions' => true
    ]);
    $this->assertEmpty($result['values']);
  }

  public function testGetReturnsOnlyCommentsOfLeaveRequestsManagedByTheCurrentLoggedInUserWhenTheLoggedInUserIsALeaveApprover() {
    $entityName = 'LeaveRequest';

    $manager = ContactFabricator::fabricate();
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();
    $contact3 = ['id' => 4];

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact1['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact2['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $leaveRequest3 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact3['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $comment1 = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest1->id,
      'entity_name' => $entityName,
      'contact_id' => $leaveRequest1->contact_id,
    ]);

    $comment2 = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest2->id,
      'entity_name' => $entityName,
      'contact_id' => $contact2['id'],
    ]);

    $comment3 = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest2->id,
      'entity_name' => $entityName,
      'contact_id' => $contact3['id'],
    ]);

    CommentFabricator::fabricate([
      'entity_id' => $leaveRequest3->id,
      'entity_name' => $entityName,
      'contact_id' => $manager['id'],
    ]);

    // Register manager in session and make sure that only the permission to use
    // the API is set (to ensure the contact won't be considered an admin)
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access AJAX API'];

    // Manager is the Leave Approver for contacts 1 and 2, but not for 3
    $this->setContactAsLeaveApproverOf($manager, $contact1);
    $this->setContactAsLeaveApproverOf($manager, $contact2);

    // The manager manages contacts 1 and 2, so all the comments added to this
    // leave request will be returned (including comment 3, which belongs to
    // contact 3, who is not managed by the logged in user)
    $result = $this->leaveRequestCommentService->get(['check_permissions' => true]);
    $this->assertCount(3, $result['values']);
    $this->assertNotEmpty($result['values'][$comment1->id]);
    $this->assertNotEmpty($result['values'][$comment2->id]);
    $this->assertNotEmpty($result['values'][$comment3->id]);

    // Leave Request 3 belongs to contact 3, which is not managed by the current
    // user, so this will return nothing
    $result = $this->leaveRequestCommentService->get([
      'leave_request_id' => $leaveRequest3->id,
      'check_permissions' => true
    ]);
    $this->assertEmpty($result['values']);
  }

  public function testGetReturnsAllTheCommentsOfLeaveRequestsWhenTheLoggedInUserIsAnAdmin() {
    $entityName = 'LeaveRequest';

    $admin = ['id' => 1];
    $contact1 = ['id' => 2];
    $contact2 = ['id' => 3];

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact1['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact2['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $comment1 = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest1->id,
      'entity_name' => $entityName,
      'contact_id' => $leaveRequest1->contact_id,
    ]);

    $comment2 = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest2->id,
      'entity_name' => $entityName,
      'contact_id' => $contact2['id'],
    ]);

    $comment3 = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest2->id,
      'entity_name' => $entityName,
      'contact_id' => $contact1['id'],
    ]);

    // Register admin in session and make sure that the admin permission is set
    $this->registerCurrentLoggedInContactInSession($admin['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [
      'access AJAX API',
      'administer leave and absences'
    ];

    // Even though the current user is not a leave manager of any
    // of the contacts and it's not the author of any of the comments,
    // all of them will still be returned
    $result = $this->leaveRequestCommentService->get(['check_permissions' => true]);
    $this->assertCount(3, $result['values']);
    $this->assertNotEmpty($result['values'][$comment1->id]);
    $this->assertNotEmpty($result['values'][$comment2->id]);
    $this->assertNotEmpty($result['values'][$comment3->id]);
  }

  public function testGetDoesNotReturnsCommentsOfSoftDeletedLeaveRequests() {
    $entityName = 'LeaveRequest';

    $contact1 = ['id' => 1];

    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact1['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    LeaveRequest::softDelete($leaveRequest1->id);

    CommentFabricator::fabricate([
      'entity_id' => $leaveRequest1->id,
      'entity_name' => $entityName,
      'contact_id' => $leaveRequest1->contact_id,
    ]);

    // Register contact1 in session and make sure that only the permission to use
    // the API is set (to ensure the contact won't be considered an admin)
    $this->registerCurrentLoggedInContactInSession($contact1['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access AJAX API'];

    // LeaveRequest 1 belongs to the current logged in user, but it has been deleted,
    // so no comments will be returned
    $result = $this->leaveRequestCommentService->get([
      'leave_request_id' => $leaveRequest1->id,
      'check_permissions' => true
    ]);
    $this->assertEmpty($result['values']);
  }

  /**
   * @expectedException UnexpectedValueException
   * @expectedExceptionMessage You must either be an L&A admin or an approver to this leave request to be able to delete the comment
   */
  public function testDeleteShouldThrowAnExceptionWhenLoggedInUserIsNotAnAdminOrLeaveApprover() {
    $contact = ContactFabricator::fabricate();

    // Register contact in session and make sure that no permission is set
    $this->registerCurrentLoggedInContactInSession($contact['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $comment = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest->id,
      'entity_name' => 'LeaveRequest',
      'contact_id' => $leaveRequest->contact_id,
    ]);

    $this->leaveRequestCommentService->delete(['comment_id' => $comment->id]);
  }

  public function testDeleteShouldDeleteCommentWhenLoggedInUserIsAnAdmin() {
    $contact = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();

    // Register contact in session and set permission to admin
    $this->registerCurrentLoggedInContactInSession($contact['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['administer leave and absences'];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $leaveContact['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $comment = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest->id,
      'entity_name' => 'LeaveRequest',
      'contact_id' => $leaveRequest->contact_id,
    ]);

    $service = new CRM_HRLeaveAndAbsences_Service_LeaveRequestComment([
      'comment_id' => $comment->id
    ]);

    $result = $this->leaveRequestCommentService->delete(['comment_id' => $comment->id]);
    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => 1,
    ];

    $this->assertEquals($expected, $result);
  }

  public function testDeleteShouldDeleteCommentWhenLoggedInUserIsTheLeaveApprover() {
    $manager = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();

    // Set logged in user as manager of Contact who requested leave
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    $this->setContactAsLeaveApproverOf($manager, $leaveContact);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $leaveContact['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $comment = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest->id,
      'entity_name' => 'LeaveRequest',
      'contact_id' => $leaveRequest->contact_id,
    ]);

    $result = $this->leaveRequestCommentService->delete(['comment_id' => $comment->id]);

    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => 1,
    ];

    $this->assertEquals($expected, $result);
  }

  public function testDeleteShouldThrowAnExceptionWhenCommentHasBeenDeletedBefore() {
    $contact = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();

    // Register contact in session and set permission to admin
    $this->registerCurrentLoggedInContactInSession($contact['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['administer leave and absences'];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $leaveContact['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $comment = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest->id,
      'entity_name' => 'LeaveRequest',
      'contact_id' => $leaveRequest->contact_id,
    ]);

    $this->leaveRequestCommentService->delete(['comment_id' => $comment->id]);
    //try delete comment again
    $this->setExpectedException('InvalidArgumentException', 'Comment does not exist or has been deleted already!');
    $this->leaveRequestCommentService->delete(['comment_id' => $comment->id]);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Comment does not exist or has been deleted already!
   */
  public function testDeleteShouldThrowAnExceptionWhenCommentDoesNotExist() {
    $contact = ContactFabricator::fabricate();

    // Register contact in session and set permission to admin
    $this->registerCurrentLoggedInContactInSession($contact['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['administer leave and absences'];

    $this->leaveRequestCommentService->delete(['comment_id' => 12]);
  }
}
