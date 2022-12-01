<?php

use App\Helpers\Sessao;
?>
    <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4">Login</h3></div>
                                    <div class="card-body">
                                    
                                        <form method="Post" action="<?=URL?>admin/login">
                                        <?=Sessao::sms('usuario')?>
                                        
                                            <div class="form-floating mb-3">
                                                <input class="form-control <?=$dados['erro_login']?'is-invalid':'' ?>" value="<?=$dados['login']?>"  type="text" id="login" placeholder="login" name="login" />
                                                <label for="login">Login</label>
                                                <div class="invalid-feedback">
                                                    <?=$dados['erro_login']?>
                                                </div>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input class="form-control <?=$dados['erro_senha']?'is-invalid':''?>"  type="password" placeholder="Password" id="senha" value="<?=$dados['senha']?>" name="senha"/>
                                                <label for="senha">Password</label>
                                                <div class="invalid-feedback">
                                                    <?=$dados['erro_senha']?>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex align-items-center justify-content-end mt-4 mb-0">
                                                
                                               <button class="btn btn-secondary" type="submit" name="btn_log" value="logar">Login</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center py-3">
                                        <div class="small">Victor Clever</div>
                                    </div>
                                </div>