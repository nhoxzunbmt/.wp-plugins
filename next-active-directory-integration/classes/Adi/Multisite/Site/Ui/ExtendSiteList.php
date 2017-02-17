<?php
if (!defined('ABSPATH')) {
	die('Access denied.');
}

if (class_exists('NextADInt_Adi_Multisite_Site_Ui_ExtendSiteList')) {
	return;
}

/**
 * Extends the "Sites" table for Multisite installations with a new column "ADI profile" which contains the assigned
 * ADI profile of every blog in the Multisite installation.
 *
 * @author Christopher Klein <ckl@neos-it.de>
 * @access public
 */
class NextADInt_Adi_Multisite_Site_Ui_ExtendSiteList
{
	const ADI_PROFILE_COLUMN = 'adi-profile';

	/**
	 * @var NextADInt_Multisite_Configuration_Persistence_BlogConfigurationRepository
	 */
	private $blogConfigurationRepository;

	/**
	 * @var NextADInt_Multisite_Configuration_Persistence_ProfileRepository
	 */
	private $profileRepository;

	/**
	 * @param NextADInt_Multisite_Configuration_Persistence_BlogConfigurationRepository $blogConfigurationRepository
	 * @param NextADInt_Multisite_Configuration_Persistence_ProfileRepository           $profileRepository
	 */
	public function __construct(NextADInt_Multisite_Configuration_Persistence_BlogConfigurationRepository $blogConfigurationRepository,
		NextADInt_Multisite_Configuration_Persistence_ProfileRepository $profileRepository
	) {
		$this->blogConfigurationRepository = $blogConfigurationRepository;
		$this->profileRepository = $profileRepository;
	}

	/**
	 * Add an 'user is disabled' indicator on the user management screen.
	 */
	public function register()
	{
		add_filter('wpmu_blogs_columns', array($this, 'addColumns'), 10, 1);
		add_action('manage_sites_custom_column', array($this, 'addContent'), 1, 2);
	}

	/**
	 * Add ADI profile column
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function addColumns($columns)
	{
		$columns[self::ADI_PROFILE_COLUMN] = __('Active ADI profile', NEXT_AD_INT_I18N);

		return $columns;
	}

	/**
	 * Add the ADI profile for the current blog
	 *
	 * @param string $columnName
	 * @param int    $blogId
	 *
	 * @return string
	 */
	public function addContent($columnName, $blogId)
	{
		if ($columnName == self::ADI_PROFILE_COLUMN) {
			$id = $this->blogConfigurationRepository->findProfileId($blogId);
			$isDefaultProfileUsed = $this->blogConfigurationRepository->isDefaultProfileUsed($blogId);

			if ($id) {
				$name = $this->profileRepository->findName($id, '');

				if ($isDefaultProfileUsed) {
					echo sprintf(__('%s (default profile)', NEXT_AD_INT_I18N), $name);

					return;
				}

				if ($name) {
					echo $name;

					return;
				}
			}

			echo "<em>" . __('None assigned', NEXT_AD_INT_I18N) . '</em>';
		}
	}
}