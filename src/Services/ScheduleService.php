<?php

namespace Soap\Jongman\Core\Services;

use App\Repositories\ScheduleRepository;
use Soap\Jongman\Core\Application\User\User;
use Soap\Jongman\Core\Factories\DailyLayoutFactory;
use Soap\Jongman\Core\Interfaces\LayoutFactoryInterface;
use Soap\Jongman\Core\Interfaces\ScheduleServiceInterface;

class ScheduleService implements ScheduleServiceInterface
{
    public function __construct(
        private ScheduleRepository $scheduleRepository,
        private ResourceService $resourceService,
        private DailyLayoutFactory $dailyLayoutFactory
    ) {}

    public function getAll($includeInaccessible, User $user)
    {
        $schedules = $this->scheduleRepository->getAll();
    }

    public function getLayout($scheduleId, LayoutFactoryInterface $layoutFactory)
    {
        return $this->scheduleRepository->getLayout($scheduleId, $layoutFactory);
    }

    public function getDailyLayout($scheduleId, LayoutFactoryInterface $layoutFactory, $reservationListing)
    {
        return $this->dailyLayoutFactory->create($reservationListing, $this->getLayout($scheduleId, $layoutFactory));
    }

    public function getSchedule($scheduleId)
    {
        return $this->scheduleRepository->loadById($scheduleId);
    }
}
