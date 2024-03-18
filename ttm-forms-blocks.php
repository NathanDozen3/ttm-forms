<?php

namespace ttm\forms;

/**
 *
 */
class Blocks {

    /**
     *
     */
    public function register_blocks() {
        register_block_type( TTM_FORMS_DIR . '/blocks/ttm-column/build' );
        register_block_type( TTM_FORMS_DIR . '/blocks/ttm-columns/build' );
        register_block_type( TTM_FORMS_DIR . '/blocks/ttm-form/build' );
        register_block_type( TTM_FORMS_DIR . '/blocks/ttm-input-date/build' );
        register_block_type( TTM_FORMS_DIR . '/blocks/ttm-input-email/build' );
        register_block_type( TTM_FORMS_DIR . '/blocks/ttm-input-hidden/build' );
        register_block_type( TTM_FORMS_DIR . '/blocks/ttm-input-password/build' );
        register_block_type( TTM_FORMS_DIR . '/blocks/ttm-input-submit/build' );
        register_block_type( TTM_FORMS_DIR . '/blocks/ttm-input-tel/build' );
        register_block_type( TTM_FORMS_DIR . '/blocks/ttm-input-text/build' );
        register_block_type( TTM_FORMS_DIR . '/blocks/ttm-textarea/build' );
    }
}
