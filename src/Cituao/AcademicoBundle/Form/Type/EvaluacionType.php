<?php
// src/Cituao/AcademicoBundle/Form/Type/EvaluacionType.php
namespace Cituao\AcademicoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EvaluacionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
		->add('proceso','textarea', array('label' => 'Proceso', 'max_length' => '1000' , 'read_only' => true, 'attr' => array('cols' => '45', 'rows' => '25')))
		->add('herramientas','textarea', array('label' => 'Herramientas', 'max_length' => '1000' , 'read_only' => true, 'attr' => array('cols' => '45', 'rows' => '25')))
		->add('proceso','textarea', array('label' => 'Proceso', 'max_length' => '1000' , 'read_only' => true, 'attr' => array('cols' => '45', 'rows' => '25')))



		->add('proceso','text', array('label' => 'Proceso:','read_only' => true))	    
		->add('nombres','text', array('label' => 'Nombres:', 'read_only' => true))
        ->add('apellidos','text', array('label' => 'Apellidos:', 'read_only' => true))
        ->add('email', 'email',  array('label' => 'Email:',  'attr' => array('placeholder' => 'usuario@servidor'), 'required' => true ))
		->add('telefonoMovil','text', array('label' => 'Teléfono móvil:', 'required' => true))
		->add('telefonoFijo','text', array('label' => 'Teléfono fijo:'))
		->add('perfil','textarea', array('label' => 'Perfil', 'max_length' => '500' , 'read_only' => true, 'attr' => array('cols' => '5', 'rows' => '5')));
		}

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cituao\AcademicoBundle\Entity\Academico', 'cascade_validation' => true
        ));
    }

    public function getName()
    {
        return 'evaluacion';
    }
}
