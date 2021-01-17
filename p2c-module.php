<?php

class p2c_category_permission
{
    const QA_USER_LEVEL_ANYONE = -1;

    /** the meta-tag we insert into the title column */
    const CATEGORY_META_KEY = 'p2c_permission_level';

    /** @var array - Cache for the category permission levels */
    var $category_permit_levels = array();

    function __construct()
    {
        $this->get_category_permit_levels();
    }

    /**
     * If category is updated without error we add/edit our permission level into the qa_categorymetas table.
     */
    function plugins_loaded()
    {
        $permit_level = qa_post_text('p2c_permit_level');
        if (qa_clicked('dosavecategory') && isset($permit_level) && !qa_clicked('docancel')) {
            $this->edit_permit_level(qa_post_text('edit'), self::CATEGORY_META_KEY, qa_post_text('p2c_permit_level'));
        }
    }

    /**
     * Uses qa_db_categorymeta_set(...) to insert or edit our permission level into the qa_categorymetas table.
     *
     * @see qa_db_categorymeta_set()
     *
     * @param string $categoryid - Category id
     * @param string $key - Inserted into the title colunm.
     * @param string $value - Inserted into the content colunm
     */
    function edit_permit_level($categoryid, $key, $value)
    {
        require_once QA_INCLUDE_DIR . 'db/metas.php'; //make sure we have access to the functions we need.

        if ((int)$value === p2c_category_permission::QA_USER_LEVEL_ANYONE) {
            qa_db_categorymeta_clear($categoryid, $key);
        } else {
            qa_db_categorymeta_set($categoryid, $key, $value);
        }
    }

    /**
     * Retrives the permission levels for catagories from the qa_categorymetas table and sets up an associative array
     * with 'category id => permission level'.
     *
     * @return array - category id => permission level
     */
    function get_category_permit_levels()
    {
        $query = 'SELECT categoryid, content FROM ^categorymetas WHERE title = $';
        $category_permissions = qa_db_read_all_assoc(qa_db_query_sub($query, self::CATEGORY_META_KEY));

        foreach ($category_permissions as $value) {
            $this->category_permit_levels[(int)$value['categoryid']] = (int)$value['content'];
        }

        return $this->category_permit_levels;
    }

    /**
     * Checks the permission level needed to access $categoryid. If no permission level exists returns 0.
     *
     * @param int $categoryid
     *
     * @return string - number which equates to the permission level required
     */
    function category_permit_level($categoryid)
    {
        return isset($this->category_permit_levels[$categoryid])
            ? $this->category_permit_levels[$categoryid]
            : null;
    }

    /**
     * Returns true if the logged in user has the required permission level to access $categoryid else false
     *
     * @param int $categoryid
     * @param mixed $userId
     * @param int $userLevel
     *
     * @return bool
     */
    function has_permit($categoryid, $userId, $userLevel)
    {
        $permit_level = $this->category_permit_level($categoryid);

        // If there is no restriction set in the given category
        if (is_null($permit_level)) {
            return true;
        }

        // If there is at least one permission that means the anonymous user will not be able to access it
        if (is_null($userId)) {
            return false;
        }

        return $userLevel >= $permit_level;
    }
}
