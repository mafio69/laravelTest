<?php

namespace External\Bar\Movies;

use External\Bar\Exceptions\ServiceUnavailableException;

class MovieService
{
    /**
     * @throws ServiceUnavailableException
     *
     * @return array
     */
    public function getTitles(): array
    {
        if (rand(0, 20) === 0) {
            throw new ServiceUnavailableException();
        }

        return [
            'titles' => [
                ['title' => "Star Wars: Episode IV - A New Hope"],
                ['title' => "The Devil and Miss Jones"],
            ]
        ];
    }
}
