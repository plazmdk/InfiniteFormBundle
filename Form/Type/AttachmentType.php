<?php

namespace Infinite\FormBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Infinite\FormBundle\Attachment\Uploader;
use Infinite\FormBundle\Form\DataTransformer\AttachmentTransformer;
use Infinite\FormBundle\Attachment\PathHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AttachmentType extends AbstractType
{
    protected $defaultSecret;
    protected $om;
    protected $pathHelper;

    public function __construct($secret, ObjectManager $om, PathHelper $pathHelper, Uploader $uploader)
    {
        $this->defaultSecret = $secret;
        $this->om            = $om;
        $this->pathHelper    = $pathHelper;
        $this->uploader      = $uploader;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', 'file', array('required' => $options['required']))
            ->add('removed', 'hidden')
            ->add('meta', 'hidden', array('required' => false))
            ->addViewTransformer(new AttachmentTransformer(
                $options,
                $this->om,
                $this->pathHelper,
                $this->uploader
            ))
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // Legacy reasons. Use $view->vars['data'] instead!
        $view->vars['attachment'] = $form->getData();
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->children['meta']->vars['value'] = $view->vars['value']['meta'];
    }

    public function getName()
    {
        return 'infinite_form_attachment';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('class'));
        $resolver->setDefaults(array(
            'secret' => $this->defaultSecret,
        ));
    }
}
