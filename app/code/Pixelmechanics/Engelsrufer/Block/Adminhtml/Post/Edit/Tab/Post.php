<?php
/**
 *
 * @category    Pixelmechanics
 * @package     Pixelmechanics Engelsrufer
 * override \Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Post 
 * Add new column "Description"
 * Created by AA 06.05.2019
 */

namespace Pixelmechanics\Engelsrufer\Block\Adminhtml\Post\Edit\Tab;

/**
 * Class Post
 * @package Pixelmechanics\Engelsrufer\Block\Adminhtml\Post\Edit\Tab
 */
class Post extends \Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Post
{  
    /**
     * Get form HTML
     *
     * @return string
     */
    public function aroundGetFormHtml(
        \Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Post $subject,
        \Closure $proceed
    )
    {
    /* table mageplaza_blog_post */
        $postData = $subject->_coreRegistry->registry('mageplaza_blog_post');
    /* get Mageplaxa blog form */
        $form = $subject->getForm();
        if (is_object($form)) {
    /* if form exist then insert column in "base_fieldset" */
            $fieldset = $form->getElement('base_fieldset');
            $fieldset->addField(
                'description',
                'editor',
                [
                   'name'   => 'description',
                    'label'  => __('Description'),
                    'title'  => __('Description'),
                    'value' => $postData->getData('description'),
                    'config' => $subject->wysiwygConfig->getConfig(['add_variables' => false, 'add_widgets' => true, 'add_directives' => true])
                ]
            );

            $subject->setForm($form);
        }

        return $proceed();
    }
}
    

