<?php

namespace Amasty\ExportCore\Api;

interface ChunkStorageInterface
{
    public function saveChunk(array $data, int $chunkId): \Amasty\ExportCore\Api\ChunkStorageInterface;
    public function readChunk(int $chunkId): array;
    public function chunkSize(int $chunkId): int;
    public function deleteChunk(int $chunkId): \Amasty\ExportCore\Api\ChunkStorageInterface;
    public function getAllChunkIds(): array;
}
