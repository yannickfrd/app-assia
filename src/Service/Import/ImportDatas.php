<?php

namespace App\Service\Import;

use App\Entity\Note;
use App\Entity\SupportGroup;
use App\Notification\MailNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class ImportDatas
{
    protected $manager;
    protected $user;
    protected $notification;

    protected $datas;

    public function __construct(
        EntityManagerInterface $manager,
        Security $security,
        MailNotification $notification)
    {
        $this->manager = $manager;
        $this->user = $security->getUser();
        $this->notification = $notification;
    }

    public function getDatas(string $fileName)
    {
        $this->datas = [];

        $row = 1;
        if (($handle = fopen($fileName, 'r')) !== false) {
            while (($data = fgetcsv($handle, 2000, ';')) !== false) {
                $num = count($data);
                ++$row;
                $row = [];
                for ($col = 0; $col < $num; ++$col) {
                    $cel = iconv('CP1252', 'UTF-8', $data[$col]);
                    $date = \DateTime::createFromFormat('d/m/Y', $cel, new \DateTimeZone(('UTC')));
                    if ($date) {
                        $cel = $date->format('Y-m-d');
                    }
                    isset($this->datas[0]) ? $row[$this->datas[0][$col]] = $cel : $row[] = $cel;
                }
                $this->datas[] = $row;
            }
            fclose($handle);
        }

        return $this->datas;
    }

    protected function findInArray($needle, array $haystack): ?int
    {
        foreach ($haystack as $key => $value) {
            if ($key === $needle) {
                return $value;
            }
        }

        return false;
    }

    protected function createNote(SupportGroup $supportGroup, string $title, string $content): Note
    {
        $contentArray = explode("\n", $content);
        $content = '<p>'.join($contentArray, '</p><p>').'</p>';

        $note = (new Note())
        ->setTitle($title)
        ->setContent($content)
        ->setSupportGroup($supportGroup)
        ->setCreatedBy($this->user)
        ->setUpdatedBy($this->user);

        $this->manager->persist($note);

        return $note;
    }

    protected function sendDuplicatedPeople(array $people)
    {
        $content = count($people).' doublons de personnes : <br/>';
        foreach ($people as $person) {
            $content = $content.$person->getLastname()."\t".$person->getFirstname()."\t".$person->getBirthdate()->format('d/m/Y').'<br/>';
        }

        $this->notification->send(
            [
                'email' => 'romain.madelaine@esperer-95.org',
                'name' => 'Romain',
            ],
            'Doublons personnes',
            $content,
        );
    }
}