<?php 
echo $this->Html->script('papers');
echo $this->Html->script('articles');
echo $this->Html->script('researchproject');

//print_r($this->request->data);

echo $this->Form->create('ResearchPaper', array('id' => 'Researchpaper_Details', 'url' => Router::url( null, true ))); ?>
<div class="main_content_header">4. Research Work</div>
<div class="main_content_header">Research Papers, if any</div>
<input type="hidden" name="modified_papers" id="modified_papers" value="false" />
<input type="hidden" name="glob_userId" id="glob_userId" value="<?php echo $this->Session->read('applicant_id'); ?>" />
<fieldset>
    <table id="paper-table">
        <thead>
            <tr>
                <th>Authors</th>
                <th>Title of the Paper</th>
                <th>Journal's Name & Place of Publication</th>
                <th>Publication & ISSN</th>
                <th>Vol./Page No/Year</th>
                <th>Impact Factor</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                if (is_array($this->request->data['Researchpaper'])) {
                    for ($key = 0; $key < count($this->request->data['Researchpaper']); $key++) {
                        echo $this->element('papers', array('key' => $key));
                    }
                }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6"></td>
                <td class="actions">
                    <a href="#" class="add">Add Row</a>
                </td>
            </tr>
        </tfoot>
    </table>
</fieldset>

<script id="paper-template" type="text/x-underscore-template">
    <?php echo $this->element('papers');?>
</script>

<div class="main_content_header">Research Articles, if any</div>
<input type="hidden" name="modified_articles" id="modified_articles" value="false" />
<fieldset>
    <table id="article-table">
        <thead>
            <tr>
                <th>Authors</th>
                <th>Title of the Book</th>
                <th>Title of the Article</th>
                <th>Place of Publication</th>
                <th>Publisher & ISBN</th>
                <th>Page No.</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                if (is_array($this->request->data['Researcharticle'])) {
                    for ($key = 0; $key < count($this->request->data['Researcharticle']); $key++) {
                        echo $this->element('article', array('key' => $key));
                    }
                }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6"></td>
                <td class="actions">
                    <a href="#" class="add">Add Row</a>
                </td>
            </tr>
        </tfoot>
    </table>
</fieldset>

<script id="article-template" type="text/x-underscore-template">
    <?php echo $this->element('article');?>
</script>

<div class="main_content_header">Research Projects, if any</div>
<input type="hidden" name="modified_rp" id="modified_rp" value="false" />
<fieldset>
    <table id="rp-table">
        <thead>
            <tr>
                <th>Title of Projects</th>
                <th>Completed / Ongoing</th>
                <th>Funding Agency</th>
                <th>As PI/CO-PI or Investigator</th>
                <th>Amount of grant</th>
                <th>Duration</th>
                <th>&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                if (is_array($this->request->data['Researchproject'])) {
                    for ($key = 0; $key < count($this->request->data['Researchproject']); $key++) {
                        echo $this->element('researchproject', array('key' => $key,
                                                                     'rp' => (!empty($this->request->data['Researchproject'][$key]['comp_ongoing']) ? $this->request->data['Researchproject'][$key]['comp_ongoing'] : 'completed' )));
                    }
                }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6"></td>
                <td class="actions">
                    <a href="#" class="add">Add Row</a>
                </td>
            </tr>
        </tfoot>
    </table>
</fieldset>

<script id="rp-template" type="text/x-underscore-template">
    <?php echo $this->element('researchproject');?>
</script>

<br/><br/>
<table width="100%" id="seminars_attended">
    <tr>
        <td width="30%" class="table_headertxt">Seminars / Conferences / Workshops / Training programmes, attended.</td>
        <td class="table_headertxt">National (No.)</td>
        <td class="table_headertxt">International (No.)</td>
        <td class="table_headertxt">Total (No.)</td>
    </tr>
    <tr>
        <td></td>
        <td><?php echo $this->Form->input('Applicant.sem_att_national', array('label' => false, 'maxlength' => '500')); ?></td>
        <td><?php echo $this->Form->input('Applicant.sem_att_international', array('label' => false, 'maxlength' => '500')); ?></td>
        <td><?php echo $this->Form->input('Applicant.sem_att_total', array('label' => false, 'maxlength' => '500')); ?></td>
    </tr>
</table>
<table width="100%" id="seminars_org">
    <tr>
        <td width="30%" class="table_headertxt">Seminars / Conferences / Workshops / Training programmes, organized.</td>
        <td class="table_headertxt">National (No.)</td>
        <td class="table_headertxt">International (No.)</td>
        <td class="table_headertxt">Total (No.)</td>
    </tr>
    <tr>
        <td></td>
        <td><?php echo $this->Form->input('Applicant.sem_org_national', array('label' => false, 'maxlength' => '500')); ?></td>
        <td><?php echo $this->Form->input('Applicant.sem_org_international', array('label' => false, 'maxlength' => '500')); ?></td>
        <td><?php echo $this->Form->input('Applicant.sem_org_total', array('label' => false, 'maxlength' => '500')); ?></td>
    </tr>
</table>
<table width="100%" id="research_guidance">
    <tr>
        <td width="30%" class="table_headertxt">Research Guidance (No. of students guided)</td>
        <td class="table_headertxt">M.Phil. / Equivalent (No.)</td>
        <td class="table_headertxt">Ph.D. (No.)</td>
    </tr>
    <tr>
        <td class="table_headertxt" style="padding-top: 20px;">Completed</td>
        <td><?php echo $this->Form->input('Applicant.rg_mphil_completed', array('label' => false, 'maxlength' => '500')); ?></td>
        <td><?php echo $this->Form->input('Applicant.rg_phd_completed', array('label' => false, 'maxlength' => '500')); ?></td>
    </tr>
    <tr>
        <td class="table_headertxt" style="padding-top: 20px;">Under supervision</td>
        <td><?php echo $this->Form->input('Applicant.rg_mphil_undersup', array('label' => false, 'maxlength' => '500')); ?></td>
        <td><?php echo $this->Form->input('Applicant.rg_phd_undersup', array('label' => false, 'maxlength' => '500')); ?></td>
    </tr>
</table>

<table width="100%" id="factors_disable">
    <tr>
        <td width="30%" class="table_headertxt"></td>
        <td class="table_headertxt">Total Impact Factor as per SCI/SCOPUS</td>
        <td class="table_headertxt"><?php echo $this->Form->input('Applicant.id', array('type', 'hidden'));
                                        echo $this->Form->input('Applicant.tot_impact_sci', array('label' => false, 'maxlength' => '500')); ?></td>
    </tr>
    <tr>
        <td class="table_headertxt"></td>
        <td class="table_headertxt">Total Impact Factor as per Google search</td>
        <td class="table_headertxt"><?php echo $this->Form->input('Applicant.tot_impact_google', array('label' => false, 'maxlength' => '500')); ?></td>
    </tr>
    <tr>
        <td class="table_headertxt"></td>
        <td class="table_headertxt">h-Index Factor as per SCOPUS</td>
        <td class="table_headertxt"><?php echo $this->Form->input('Applicant.h_index_scopus', array('label' => false, 'maxlength' => '500')); ?></td>
    </tr>
    <tr>
        <td class="table_headertxt"></td>
        <td class="table_headertxt">h-Index Factor as per Google search</td>
        <td class="table_headertxt"><?php echo $this->Form->input('Applicant.h_index_google', array('label' => false, 'maxlength' => '500')); ?></td>
    </tr>
    <tr>
        <td class="table_headertxt"></td>
        <td class="table_headertxt">i-10 Index Factor as per Google search</td>
        <td class="table_headertxt"><?php echo $this->Form->input('Applicant.i10_index_google', array('label' => false, 'maxlength' => '500')); ?></td>
    </tr>
</table>

<div class="submit">
    <?php echo $this->Form->submit('Save & Continue', array('id' => 'formSubmit' , 'div' => false)); ?>
    <?php echo $this->Form->submit('Cancel', array('name' => 'Cancel', 'div' => false)); ?>
</div>

<?php echo $this->Form->end(); ?>