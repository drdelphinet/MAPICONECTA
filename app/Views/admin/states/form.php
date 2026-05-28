<section class="page-header">
    <p class="eyebrow">Administracao territorial</p>
    <h1><?= $state ? 'Editar estado' : 'Novo estado'; ?></h1>
    <p>Cadastro base para suportar o Piauí agora e expansao futura para todo o Brasil.</p>
</section>

<section class="form-card">
    <form method="post" action="<?= e($formAction); ?>" class="stack-form">
        <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">

        <div class="form-grid">
            <label class="field">
                <span>Codigo IBGE</span>
                <input type="number" name="codigo_ibge" value="<?= e((string) ($state['codigo_ibge'] ?? '')); ?>" required>
            </label>

            <label class="field">
                <span>Sigla</span>
                <input type="text" name="sigla" maxlength="2" value="<?= e((string) ($state['sigla'] ?? '')); ?>" required>
            </label>

            <label class="field">
                <span>Nome</span>
                <input type="text" name="nome" value="<?= e((string) ($state['nome'] ?? '')); ?>" required>
            </label>

            <label class="field">
                <span>Slug</span>
                <input type="text" name="slug" value="<?= e((string) ($state['slug'] ?? '')); ?>" placeholder="gerado automaticamente se vazio">
            </label>

            <label class="field">
                <span>Regiao</span>
                <input type="text" name="regiao" value="<?= e((string) ($state['regiao'] ?? '')); ?>" required>
            </label>

            <label class="field">
                <span>Status</span>
                <select name="status">
                    <?php foreach (['rascunho', 'ativo', 'inativo'] as $status): ?>
                        <option value="<?= e($status); ?>" <?= (($state['status'] ?? 'ativo') === $status) ? 'selected' : ''; ?>><?= e(ucfirst($status)); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>

        <div class="toolbar-actions">
            <button type="submit" class="button">Salvar</button>
            <a class="button button-outline" href="<?= e(url('admin/estados')); ?>">Voltar</a>
        </div>
    </form>
</section>
