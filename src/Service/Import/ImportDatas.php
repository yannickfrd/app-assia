<?php

namespace App\Service\Import;

use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use App\Notification\ImportNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class ImportDatas
{
    protected $em;
    protected $user;
    protected $importNotification;

    protected $datas;

    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        ImportNotification $importNotification)
    {
        $this->em = $em;
        $this->user = $security->getUser();
        $this->importNotification = $importNotification;
    }

    public function getDatas(string $fileName): array
    {
        $this->datas = [];

        $nbRows = 1;
        if (false !== ($handle = fopen($fileName, 'r'))) {
            while (false !== ($data = fgetcsv($handle, 2000, ';'))) {
                $num = count($data);
                ++$nbRows;
                $rows = [];
                for ($col = 0; $col < $num; ++$col) {
                    $cel = iconv('CP1252', 'UTF-8', $data[$col]);
                    $date = \DateTime::createFromFormat('d/m/Y', $cel, new \DateTimeZone('UTC'));
                    if ($date) {
                        $cel = $date->format('Y-m-d');
                    }
                    isset($this->datas[0]) ? $rows[$this->datas[0][$col]] = $cel : $rows[] = $cel;
                }
                $this->datas[] = $rows;
            }
            fclose($handle);
        }

        return $this->datas;
    }

    protected function findInArray($needle, array $haystack): ?int
    {
        if (!isset($needle)) {
            return null;
        }

        foreach ($haystack as $key => $value) {
            if ($key === $needle) {
                return $value;
            }
        }

        return null;
    }

    protected function createNote(SupportGroup $supportGroup, string $title, string $content): Note
    {
        $contentArray = explode("\n", $content);
        $content = '<p>'.join('</p><p>', $contentArray).'</p>';

        $note = (new Note())
        ->setTitle($title)
        ->setContent($content)
        ->setSupportGroup($supportGroup)
        ->setCreatedBy($this->user)
        ->setUpdatedBy($this->user);

        $this->em->persist($note);

        return $note;
    }

    protected function sendDuplicatedPeople(array $people): void
    {
        $content = count($people).' doublons de personnes : <br/>';
        foreach ($people as $person) {
            $content = $content.$person->getLastname()."\t".$person->getFirstname()."\t".$person->getBirthdate()->format('d/m/Y').'<br/>';
        }

        $this->importNotification->sendNotif($content);
    }
}
