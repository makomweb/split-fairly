<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Form\DataTransformer\JsonToStringTransformer;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractCrudController<Event>
 *
 * Admin interface for viewing and managing domain events stored in event sourcing.
 * Most event fields are immutable (id, createdAt, createdBy, subjectType, subjectId, eventType),
 * but the payload can be edited for correcting event data, and events can be deleted if needed.
 */
class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Event')
            ->setEntityLabelInPlural('Events')
            ->setSearchFields(['createdBy', 'subjectType', 'subjectId', 'eventType'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle(Crud::PAGE_INDEX, 'Events')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Event Details')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Event');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm()
            ->setHelp('UUID v7');

        yield DateTimeField::new('createdAt')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss');

        yield TextField::new('createdBy')
            ->hideOnForm()
            ->setHelp('User UUID who triggered this event');

        yield TextField::new('subjectType')
            ->hideOnForm()
            ->setHelp('Type of entity affected (e.g., Group, Expense)');

        // yield TextField::new('subjectId')
        //     ->hideOnForm()
        //     ->setHelp('ID of the entity affected');

        yield TextField::new('eventType')
            ->hideOnForm()
            ->setHelp('Type of domain event (e.g., ExpenseCreated, ExpenseUpdated)');

        // Note: payload field is added manually in form builders to avoid EasyAdmin's field configurator
        // trying to convert the array value to a string during field configuration
    }

    public function createEditFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        AdminContext $context,
    ): FormBuilderInterface {
        $builder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        $this->addPayloadField($builder);

        return $builder;
    }

    public function createNewFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        AdminContext $context,
    ): FormBuilderInterface {
        $builder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        $this->addPayloadField($builder);

        return $builder;
    }

    private function addPayloadField(FormBuilderInterface $builder): void
    {
        $builder->add('payload', TextareaType::class, [
            'label' => 'Payload',
            'help' => 'Event payload (JSON format)',
            'attr' => ['style' => 'font-family: monospace; height: 300px'],
            'required' => false,
        ]);

        $builder->get('payload')->addModelTransformer(new JsonToStringTransformer());
    }
}
