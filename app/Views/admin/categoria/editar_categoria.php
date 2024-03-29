<?php 
use App\Helpers\Sessao;
?>

<link rel="stylesheet" href="<?= asset("css/admin/style3.css") ?>">
<section class="section">
    <?= Sessao::izitoast('categoriaA'); ?>
    <?= Sessao::izitoast('categoriaS'); ?>
    <?= Sessao::izitoast('categoriaE'); ?>
    <?= Sessao::sms('edit'); ?>
    <?= Sessao::sms('metodo'); ?>
    <h1>Categorias / Actualizar</h1>
    
<main>
        
        <section class="container mt-5 mb-5">
            <form action="<?=URL?>/admin/categoria/edit/<?=$edit['id']?>" method="POST">
                <div class="row">
                    <div class="form-group col-12 col-md-6">
                        <label for="nome">Nome*</label>
                        <input type="text" class="form-control <?=$dados['erro_nome']? 'is-invalid' :'' ?>" value="<?=$edit['nome']?>" id="nome" name="nome" placeholder="Digite o nome da categoria..." >
                        <div class="invalid-feedback">
                            <?=$dados['erro_nome']?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-12 col-md-6">
                        <label for="descricao">Descrição*</label>
                        <input type="text" class="form-control <?=$dados['erro_descricao']?'is-invalid':'' ?>" id="descricao" name="descricao" placeholder="Digite a descrição da categoria..."  value="<?=$edit['descricao']?>">
                        <div class="invalid-feedback">
                            <?=$dados['erro_descricao']?>
                        </div>
                    </div>
                    <div class="form-group col-12 col-md-6">
                        <label for="status">Status*</label>
                        <select class="form-control fs-5" id="status" name="status">
                            <option value="1">Ativo</option>
                            <option value="0">Desativado</option>
                        </select>
                    </div>
                </div><br>
                <button type="submit" class="btn btn-primary" name="btn_save" value="salvar">Actualizar</button>
            </form>
        </section>
    </main>