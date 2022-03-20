<?php

namespace SpawnComposerRepository\Services;

use RuntimeException;

class ComposerPackageCreator
{

    protected array $repositories = [];

    public function addVersionToRepository(string $repository, string $version, array $definition): void {
        if(!$this->validateVersion($definition)) {
            throw new RuntimeException('Invalid version definition passed!');
        }

        if(!isset($this->repositories[$repository])) {
            $this->repositories[$repository] = [];
        }

        $this->repositories[$repository][$version] = $this->prepareVersionDefinition($definition, $repository, $version);

    }

    public function getDefinition(): array {
        return [
            'packages' => $this->createPackages()
        ];
    }

    protected function createPackages(): array {
        $packages = [];

        foreach($this->repositories as $repository => $versions) {
            $packages[$repository] = $this->getRepository($versions);
        }

        return $packages;
    }

    protected function getRepository(array $versions): array {
        $repository = [];

        foreach($versions as $version => $versionData) {
            $repository[$version] = $this->getVersionData($versionData);
        }

        return $repository;
    }

    protected function getVersionData(array $version): array {
        $versionData = [
            'name' => $version['name'],
            'version' => $version['version'],
            'version_normalized' => $version['version_normalized'],
            'source' => [
                'type' => $version['source']['type'],
                'url' => $version['source']['url'],
                'reference' => $version['source']['reference'],
            ],
            'dist' => [
                'type' => $version['dist']['type'],
                'url' => $version['dist']['url'],
                'reference' => $version['dist']['reference'],
                'shasum' => $version['dist']['shasum'],
            ],
            'time' => $version['time'],
            'type' => $version['type'],
            'autoload' => $this->getAutoload($version['autoload'] ?? []),
            'description' => $version['description']
        ];

        return $versionData;
    }

    protected function getAutoload(array $data): array {
        $autoloadData = [];

        if(isset($data['psr-4'])) {
            foreach($data['psr-4'] as $namespace => $directory) {
                $versionData['psr-4'][$namespace] = $directory;
            }
        }

        return $autoloadData;
    }

    protected function validateVersion(array $versionData): bool {
        return isset(
           $versionData['source']['type'],
           $versionData['source']['url'],
           $versionData['source']['reference'],
           $versionData['dist']['type'],
           $versionData['dist']['url'],
           $versionData['dist']['reference'],
           $versionData['dist']['shasum'],
           $versionData['time'],
           $versionData['type'],
           $versionData['description'],
        );
    }

    protected function prepareVersionDefinition(array $versionDefinition, string $repository, string $version): array {

        $versionDefinition['name'] = $repository;
        $versionDefinition['version'] = $version;
        $versionDefinition['version_normalized'] = $this->normalizeVersion($version);

        return $versionDefinition;
    }

    protected function normalizeVersion(string $version): string {

        if($version === 'dev-master' || $version === 'dev-main') {
            return '9999999-dev';
        }

        if(strpos($version, 'dev-') === 0) {
            return $version;
        }

        //tag

        // v1.02.0
        $version = ltrim($version, 'v');
        // 1.02.0
        $parts = explode('.',$version, 5);
        if(isset($parts[4])) {
            unset($parts[4]);
        }
        // [1,02,0]
        while(count($parts) < 4) {
            $parts[] = '0';
        }
        // [1,02,0,0]
        $version = implode('.', array_map(static function($part) {
            return (string)(int)$part;
        }, $parts));
        // 1.2.0.0

        return $version;
    }

}

