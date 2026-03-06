<?php

namespace App\Controller\Admin;

use App\Entity\Event as EventEntity;
use App\Form\DataTransformer\JsonToStringTransformer;
use App\SplitFairly\EventStoreInterface;
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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @extends AbstractCrudController<EventEntity>
 *
 * Admin interface for viewing and managing domain events stored in event sourcing.
 * Most event fields are immutable (id, createdAt, createdBy, subjectType, subjectId, eventType),
 * but the payload can be edited for correcting event data, and events can be deleted if needed.
 */
class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EventEntity::class;
    }

    public function __construct(private EventStoreInterface $eventRepository)
    {
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
        $wipe = Action::new('wipeEvents', 'Wipe Events', 'fa fa-trash')
            ->addCssClass('btn btn-danger')
            ->linkToCrudAction('wipeEvents');

        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $wipe);
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

    /** @param AdminContext<EventEntity> $context */
    #[IsGranted('ROLE_ADMIN')]
    public function wipeEvents(AdminContext $context): RedirectResponse
    {
        $this->eventRepository->reset();
        $this->addFlash('success', 'All events have been deleted.');

        $referrer = $context->getRequest()->headers->get('referer');
        $route = $referrer ?? $this->generateUrl('admin_event_index');

        return $this->redirect($route);
    }
}
