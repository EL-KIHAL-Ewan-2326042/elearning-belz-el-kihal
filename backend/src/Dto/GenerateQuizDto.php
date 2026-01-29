<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class GenerateQuizDto
{
    #[Groups(['course:write'])]
    #[Assert\Range(min: 1, max: 20)]
    public int $questionCount = 10;
}
