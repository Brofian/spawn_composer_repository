<?php

namespace SpawnComposerRepository\Services;


use SpawnCore\System\Custom\Gadgets\JsonHelper;

class GithubWebhookInterpreter
{

    protected array $webhookData;
    protected array $branches;
    protected array $tags;

    public function __construct(string $webhookData)
    {
        $this->webhookData = JsonHelper::jsonToArray($webhookData);

        $this->loadBranches($this->webhookData['repository']['branches_url']);
        $this->loadTags($this->webhookData['repository']['tags_url']);
    }

    public function getTime(): string {
        return $this->webhookData['head_commit']['timestamp'];
    }

    public function getRepositoryName(): string {
        return $this->webhookData['repository']['full_name'];
    }

    public function getRemoteUrl(): string {
        return $this->webhookData['repository']['ssh_url'];
    }

    public function getDownloadUrl(string $branch, bool $useTar = false): string {
        return sprintf('%s/%s/%s',
            $this->webhookData['repository']['svn_url'],
            $useTar ? 'tarball' : 'zipball',
            $branch
        );
    }

    public function getBranches(): array {
        return $this->branches;
    }

    public function getTags(): array {
        return $this->tags;
    }

    protected function loadBranches(string $url): void {
        $url = preg_replace("/\{.*\}/", '', $url);
        $data = $this->doRestCall($url, 'GET', [
            'User-Agent' => 'request'
        ]);
        $branchData = JsonHelper::jsonToArray($data);
        $this->branches = [];
        foreach($branchData as $branch) {
            $this->branches[$branch['name']] = [
                'name' => $branch['name'],
                'sha' => $branch['commit']['sha'],
                'url' => $branch['commit']['url'],
            ];
        }
    }

    protected function loadTags(string $url): void {
        $url = preg_replace('/\{.*\}/', '', $url);
        $data = $this->doRestCall($url, 'GET', [
            'User-Agent' => 'request'
        ]);
        $tagData = JsonHelper::jsonToArray($data);

        $this->tags = [];
        foreach($tagData as $tag) {
            $this->tags[$tag['name']] = [
                'name' => $tag['name'],
                'sha' => $tag['commit']['sha'],
                'url' => $tag['commit']['url'],
                'zip' => $tag['zipball_url'],
                'tar' => $tag['tarball_url'],
            ];
        }
    }


    protected function doRestCall(string $url, string $method = 'GET', array $headers = []): string {
        $options = [
            'http' => [
                'method' => $method
            ]
        ];
        foreach($headers as $header => $value) {
            if(!isset($options['http']['header'])) {
                $options['http']['header'] = '';
            }

            $options['http']['header'] .= "$header: $value\r\n";
        }

        return file_get_contents($url, false, stream_context_create($options));
    }

}

