<?php
/**
 * @author AlexanderC
 */

namespace PStorage;


interface Definition
{
    const PK = 0x001;

    const ONE = 0x002;
    const MANY = 0x004;

    const REQUIRED = 0x008;
    const UNIQUE = 0x010;
}