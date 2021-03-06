<?php
namespace common\tests\unit\models;

use yii\db\ActiveQuery;
use common\models\Version;
use common\models\Project;
use common\models\Screen;
use common\tests\fixtures\UserFixture;
use common\tests\fixtures\ProjectFixture;
use common\tests\fixtures\VersionFixture;
use common\tests\fixtures\ScreenFixture;

/**
 * Version AR model tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class VersionTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * @inheritdoc
     */
    public function _before()
    {
        $this->tester->haveFixtures([
            'user' => [
                'class'    => UserFixture::className(),
                'dataFile' => codecept_data_dir() . 'user.php',
            ],
            'project' => [
                'class'    => ProjectFixture::className(),
                'dataFile' => codecept_data_dir() . 'project.php',
            ],
            'version' => [
                'class'    => VersionFixture::className(),
                'dataFile' => codecept_data_dir() . 'version.php',
            ],
            'screen' => [
                'class'    => ScreenFixture::className(),
                'dataFile' => codecept_data_dir() . 'screen.php',
            ],
        ]);
    }

    /**
     * `Version::getProject()` relation query method test.
     */
    public function testGetProject()
    {
        $model = Version::findOne(1001);
        $query = $model->getProject();

        verify($query)->isInstanceOf(ActiveQuery::className());
        verify('Should be hasOne relation', $query->multiple)->false();
        verify('Query result should be valid Project model', $model->project)->isInstanceOf(Project::className());
        verify('Query result project id should match', $model->project->id)->equals($model->projectId);
    }

    /**
     * `Version::getScreens()` relation query method test.
     */
    public function testGetScreens()
    {
        $this->specify('Version WITHOUT related Screen models', function() {
            $model  = Version::findOne(1004);
            $query = $model->getScreens();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should be an empty array', $model->screens)->count(0);
        });

        $this->specify('Version WITH related Screen models', function() {
            $model = Version::findOne(1001);
            $query = $model->getScreens();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasMany relation', $query->multiple)->true();
            verify('Query result should not be empty', $model->screens)->notEmpty();
            foreach ($model->screens as $screen) {
                verify('Query result item should be valid Screen model', $screen)->isInstanceOf(Screen::className());
                verify('Query result item version id should match', $screen->versionId)->equals($model->id);
            }
        });
    }

    /**
     * `Version::getLastScreen()` relation query method test.
     */
    public function testGetLastScreen()
    {
        $this->specify('Version WITHOUT related Screen model', function() {
            $model  = Version::findOne(1004);
            $query = $model->getLastScreen();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasOne relation', $query->multiple)->false();
            verify('Query result should be null', $model->lastScreen)->null();
        });

        $this->specify('Version WITH related Screen model', function() {
            $model = Version::findOne(1001);
            $query = $model->getLastScreen();

            verify($query)->isInstanceOf(ActiveQuery::className());
            verify('Should be hasOne relation', $query->multiple)->false();
            verify('Query result should be valid Screen model', $model->lastScreen)->isInstanceOf(Screen::className());
            verify('Query result versionId should match', $model->lastScreen->versionId)->equals($model->id);
            verify('Query result id should match', $model->lastScreen->id)->equals(1002);
        });
    }

    /**
     * `Version::isTheOnlyOne()` method test.
     */
    public function testIsTheOnlyOne()
    {
        $this->specify('Project with more than one versions', function() {
            $version = Version::findOne(1001);
            verify($version->isTheOnlyOne())->false();
        });

        $this->specify('Project with only one version', function() {
            $version = Version::findOne(1003);
            verify($version->isTheOnlyOne())->true();
        });
    }
}
