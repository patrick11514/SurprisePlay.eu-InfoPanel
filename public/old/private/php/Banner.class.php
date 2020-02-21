<?php
namespace ZeroCz\Banner;

interface Banner
{
    /**
     * Aktuální počet na serveru
     * 
     * @return int
     */
    public function getOnlinePlayers();

    /**
     * Maximální počet hráčů, kteří se mohou připojit na server
     * 
     * @return int
     */
    public function getMaxPlayers();
}