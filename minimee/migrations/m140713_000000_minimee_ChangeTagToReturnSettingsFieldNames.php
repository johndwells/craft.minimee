<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m140713_000000_minimee_ChangeTagToReturnSettingsFieldNames extends BaseMigration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        // Get original settings
        $query = craft()->db->createCommand()
            ->select('settings')
            ->from('plugins')
            ->where('class="Minimee"')
        ;
        $oldSettings = $query->queryRow();
        $settings = json_decode($oldSettings['settings'], true);

        // Change setting names
        $settings['cssReturnTemplate'] = array_key_exists('cssTagTemplate', $settings) ? $settings['cssTagTemplate'] : '';
        $settings['jsReturnTemplate'] = array_key_exists('jsTagTemplate', $settings) ? $settings['jsTagTemplate'] : '';
        unset($settings['cssTagTemplate']);
        unset($settings['jsTagTemplate']);

        // Update settings field
        $newSettings = json_encode($settings);
        $this->update('plugins', array('settings'=>$newSettings), 'class="Minimee"');
        return true;
    }
}
