<div class="auth-form-header">
    <p class="eyebrow">Acesso</p>
    <h1>Entrar no sistema</h1>
    <p>Use um usuario cadastrado para acessar o Meu MAPI ou o painel administrativo.</p>
</div>

<form method="post" action="<?= e(url('entrar')); ?>" class="stack-form">
    <input type="hidden" name="_token" value="<?= e(\App\Core\Csrf::token()); ?>">

    <label class="field">
        <span>Email</span>
        <input type="email" name="email" value="<?= e((string) old('email')); ?>" placeholder="voce@exemplo.com" required>
    </label>

    <label class="field">
        <span>Senha</span>
        <input type="password" name="password" placeholder="Sua senha" required>
    </label>

    <button type="submit" class="button">Entrar</button>
</form>

<div class="auth-help">
    <p>Depois de importar o banco, use o usuario administrador inicial definido no arquivo <code>database/schema.sql</code>.</p>
    <p><strong>Seed padrao:</strong> <code>admin@mapiconecta.local</code> / <code>admin123</code></p>
    <a href="<?= e(url('')); ?>">Voltar para a pagina inicial</a>
</div>
