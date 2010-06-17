<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.User.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.FollowMySQLDAO.php';

class TestOfFollowMySQLDAO extends ThinkTankUnitTestCase {
    protected $DAO;
    protected $logger;
    public function TestOfFollowMySQLDAO() {
        $this->UnitTestCase('FollowMySQLDAO class test');
    }

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->DAO = new FollowMySQLDAO();

        //Insert test data into test table
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, follower_count, friend_count) VALUES (1234567890, 'jack', 'Jack Dorsey', 'avatar.jpg', 150210, 124);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, last_updated) VALUES (1324567890, 'ev', 'Ev Williams', 'avatar.jpg', '1/1/2005');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected, follower_count, friend_count) VALUES (1623457890, 'private', 'Private Poster', 'avatar.jpg', 1, 35342, 1345);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_user_errors (user_id, error_code, error_text, error_issued_to_user_id) VALUES (15, 404, 'User not found', 1324567890);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (1324567890, 1234567890, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (1324567890, 14, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (1324567890, 15, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (1324567890, 1623457890, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (1623457890, 1324567890, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);


        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen) VALUES (1623457890, 1234567890, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_follows (user_id, follower_id, active, last_seen) VALUES (14, 1234567890, 0, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_follows (user_id, follower_id, active, last_seen) VALUES (1324567890, 17, 0, '2006-01-08 23:54:41');";
        PDODAO::$PDO->exec($q);
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
        $this->DAO = null;
    }

    public function testFollowExists() {
        $this->assertTrue($this->DAO->followExists(1324567890, 1234567890));
        $this->assertFalse($this->DAO->followExists(1234567890, 1324567890));
    }

    public function testUpdate() {
        $this->assertEqual($this->DAO->update(1234567890, 1324567890), 0);
        $this->assertEqual($this->DAO->update(1324567890, 1234567890), 1);
    }

    public function testDeactivate() {
        $this->assertEqual($this->DAO->deactivate(1234567890, 1324567890), 0);
        $this->assertEqual($this->DAO->deactivate(1324567890, 1234567890), 1);
    }

    public function testInsert() {
        $this->assertEqual($this->DAO->insert(1234567890, 14), 1);
        $this->assertTrue($this->DAO->followExists(1234567890, 14));
    }

    public function testGetUnloadedFollowerDetails() {
        $unloaded_followers = $this->DAO->getUnloadedFollowerDetails(1324567890);

        $this->assertIsA($unloaded_followers, "array");
        $this->assertEqual(count($unloaded_followers), 2);
        $this->assertEqual($unloaded_followers[0]['follower_id'], 17);
        $this->assertEqual($unloaded_followers[1]['follower_id'], 14);
    }

    public function testCountTotalFollowsWithErrors() {
        $total_follower_errors = $this->DAO->countTotalFollowsWithErrors(1324567890);

        $this->assertIsA($total_follower_errors, "int");
        $this->assertEqual($total_follower_errors, 1);
    }

    public function testCountTotalFriendsWithErrors() {
        $total_friend_errors = $this->DAO->countTotalFriendsWithErrors(1324567890);

        $this->assertIsA($total_friend_errors, "int");
        $this->assertEqual($total_friend_errors, 0);
    }

    public function testCountTotalFollowsWithFullDetails() {
        $total_follows_with_details = $this->DAO->countTotalFollowsWithFullDetails(1324567890);

        $this->assertIsA($total_follows_with_details, "int");
        $this->assertEqual($total_follows_with_details, 2);
    }

    public function testCountTotalFollowsProtected() {
        $total_follows_protected = $this->DAO->countTotalFollowsProtected(1324567890);

        $this->assertIsA($total_follows_protected, "int");
        $this->assertEqual($total_follows_protected, 1);
    }

    public function testCountTotalFriends() {
        $total_friends = $this->DAO->countTotalFriends(1234567890);

        $this->assertIsA($total_friends, "int");
        $this->assertEqual($total_friends, 3);
    }

    public function testCountTotalFriendsProtected() {
        $total_friends_protected = $this->DAO->countTotalFriendsProtected(1234567890);

        $this->assertIsA($total_friends_protected, "int");
        $this->assertEqual($total_friends_protected, 1);
    }

    public function testGetStalestFriend() {
        $stalest_friend = $this->DAO->getStalestFriend(1234567890);

        $this->assertNotNull($stalest_friend);
        $this->assertEqual($stalest_friend->user_id, 1324567890);
        $this->assertEqual($stalest_friend->username, 'ev');
    }

    public function testGetOldestFollow() {
        $q = "INSERT INTO tt_follows (user_id, follower_id, last_seen, active) VALUES (930061, 20, '2001-04-08 23:54:41', 1);";
        PDODAO::$PDO->exec($q);

        $oldest_follow = $this->DAO->getOldestFollow();

        $this->assertNotNull($oldest_follow);
        $this->assertEqual($oldest_follow["followee_id"], 930061);
        $this->assertEqual($oldest_follow["follower_id"], 20);
    }

    public function testGetMostFollowedFollowers(){
        $result = $this->DAO->getMostFollowedFollowers(1324567890, 20);

        $this->assertEqual($result[0]["user_id"], 1234567890);
        $this->assertEqual($result[1]["user_id"], 1623457890);
    }

    public function testGetLeastLikelyFollowers(){
        $result = $this->DAO->getLeastLikelyFollowers(1324567890, 15);
        
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]["user_id"], 1234567890);
        $this->assertEqual($result[1]["user_id"], 1623457890);
    }

    public function testGetEarliestJoinerFollowers(){
        $result = $this->DAO->getEarliestJoinerFollowers(1324567890);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['user_id'], 1234567890);
        $this->assertEqual($result[1]['user_id'], 1623457890);
    }

    public function testGetMostActiveFollowees(){
        $result = $this->DAO->getMostActiveFollowees(1234567890);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['user_id'], 1324567890);
        $this->assertEqual($result[1]['user_id'], 1623457890);
    }

    public function testGetFormerFollowees(){
        $result = $this->DAO->getFormerFollowees(17);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]['user_id'], 1324567890);
    }

    public function testGetFormerFollowers(){
        $result = $this->DAO->getFormerFollowers(14);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]['user_id'], 1234567890);
    }

    public function testGetLeastActiveFollowees(){
        $result = $this->DAO->getLeastActiveFollowees(1234567890);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['user_id'], 1324567890);
        $this->assertEqual($result[1]['user_id'], 1623457890);
    }

    public function testGetMostFollowedFollowees(){
        $result = $this->DAO->getMostFollowedFollowees(1234567890);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['user_id'], 1623457890);
        $this->assertEqual($result[1]['user_id'], 1324567890);
    }

    public function testGetMutualFriends(){
        $result = $this->DAO->getMutualFriends(1324567890, 1234567890);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 1);
        $this->assertEqual($result[0]['user_id'], 1623457890);
    }

    public function testGetFriendsNotFollowingBack(){
        $result = $this->DAO->getFriendsNotFollowingBack(1234567890);

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 2);
        $this->assertEqual($result[0]['user_id'], 1324567890);
        $this->assertEqual($result[1]['user_id'], 1623457890);
    }

}
?>